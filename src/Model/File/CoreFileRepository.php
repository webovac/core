<?php

declare(strict_types=1);

namespace Webovac\Core\Model\File;

use App\Model\Article\Article;
use App\Model\File\File;
use App\Model\File\FileData;
use App\Model\File\FileRepository;
use App\Model\Page\Page;
use App\Model\Web\Web;
use Choowx\RasterizeSvg\Svg;
use Nette\Http\FileUpload;
use Nette\Utils\Image;
use Nette\Utils\ImageColor;
use Nette\Utils\ImageException;
use Nette\Utils\ImageType;
use Nette\Utils\Random;
use Nette\Utils\UnknownImageFileException;
use Webovac\Core\Model\CmsEntity;


trait CoreFileRepository
{
//	public function getByData(FileData|string $data): ?File
//	{
//		if ($data instanceof FileData) {
//			if (!isset($data->identifier)) {
//				return null;
//			}
//			return $this->getBy(['identifier' => $data->identifier]);
//		}
//		return $this->getBy(['identifier' => $data]);
//	}

	public function getByData(FileData $data, ?CmsEntity $entity = null): ?File
	{
		if ($data instanceof FileData) {
			if (!isset($data->identifier)) {
				return null;
			}
//			return $this->getBy([
//				'identifier' => $data->identifier,
////				'web' => $entity instanceof Web ? $entity : null,
////				'article' => $entity instanceof Article ? $entity : null,
////				'page' => $entity instanceof Page ? $entity : null,
//			]);
			return $this->getBy([
				'identifier' => $data->identifier,
				'web' => $entity instanceof Web ? $entity : null,
				'article' => $entity instanceof Article ? $entity : null,
				'page' => $entity instanceof Page ? $entity : null,
			]);
		}
		return $this->getBy(['identifier' => $data]);
	}


	public function createFileData(FileData $data, ?string $key = null): ?FileData
	{
		if (!isset($data->upload)) {
			return $data;
		}
		if (is_string($data->upload)) {
			$data->upload = $this->createFileUploadFromString($data->upload);
		}
		if (!$data->upload->hasFile()) {
			return null;
		}
		if ($key) {
			$keyProperty = $data::getKeyProperty();
			if ($keyProperty) {
				$data->$keyProperty = $key;
			}
		}
		$identifier = $this->fileUploader->upload($data->upload);
		$file = $this->getModel()->getRepository(FileRepository::class)->getBy(['identifier' => $identifier]);
		$data->name = $data->upload->getSanitizedName();
		$data->extension = $data->upload->getSuggestedExtension();
		if (!$file) {
			$data->identifier = $identifier;
			$data->contentType = $data->upload->getContentType();
			$data->type = $data->upload->getContentType() === 'image/svg+xml' ? File::TYPE_SVG : ($data->upload->isImage() ? File::TYPE_IMAGE : File::TYPE_FILE);
			if ($data->upload->getContentType() === 'image/svg+xml') {
				$compatibleUpload = $this->svg2png($data->upload, $data->forceSquare);
				$image = Image::fromFile($compatibleUpload->getTemporaryFile());
				$data->compatibleIdentifier = $this->fileUploader->upload($compatibleUpload);
				$modernUpload = $this->image2webp($compatibleUpload, $data->forceSquare);
				$data->modernIdentifier = $this->fileUploader->upload($modernUpload);
				$data->width = $image->getWidth();
				$data->height = $image->getHeight();
			} elseif ($data->upload->getContentType() === 'image/webp' || $data->upload->getContentType() === 'image/avif') {
				$compatibleUpload = $this->image2jpeg($data->upload, $data->forceSquare);
				$data->compatibleIdentifier = $this->fileUploader->upload($compatibleUpload);
				$data->modernIdentifier = $identifier;
			} elseif ($data->upload->isImage()) {
				$modernUpload = $this->image2webp($data->upload, $data->forceSquare);
				$data->compatibleIdentifier = $identifier;
				$data->modernIdentifier = $this->fileUploader->upload($modernUpload);
			} elseif ($data->upload->getContentType() === 'application/pdf') {
				$compatibleUpload = $this->pdf2jpeg($data->upload, $data->forceSquare);
				$data->compatibleIdentifier = $this->fileUploader->upload($compatibleUpload);
				$modernUpload = $this->image2webp($compatibleUpload, $data->forceSquare);
				$data->modernIdentifier = $this->fileUploader->upload($modernUpload);
			}
		} else {
			$originalFileData = $file->getData();
			$data->identifier = $originalFileData->identifier;
			$data->contentType = $originalFileData->contentType;
			$data->type = $originalFileData->type;
			$data->compatibleIdentifier = $originalFileData->compatibleIdentifier;
			$data->modernIdentifier = $originalFileData->modernIdentifier;
		}
		if ($data->upload?->isImage()) {
			$image = Image::fromFile($data->upload->getTemporaryFile());
			$data->width = $image->getWidth();
			$data->height = $image->getHeight();
		}
		return $data;
	}


	public function svg2png(FileUpload $upload, bool $forceSquare): FileUpload
	{
		$tmpFile = $upload->getTemporaryFile();
		$cloneFile = $this->dir->getTempDir() . '/' . Random::generate(8);
		copy($tmpFile, $cloneFile);
		Svg::make(file_get_contents($cloneFile))->saveAsPng($cloneFile);
		$upload = $this->createFileUploadFromFile($cloneFile);
		return $forceSquare ? $this->image2square($upload) : $upload;
	}


	/**
	 * @throws ImageException
	 * @throws UnknownImageFileException
	 */
	public function image2webp(FileUpload $upload, bool $forceSquare): FileUpload
	{
		$tmpFile = $upload->getTemporaryFile();
		$cloneFile = $this->dir->getTempDir() . '/' . Random::generate(8);
		copy($tmpFile, $cloneFile);
		Image::fromFile($cloneFile)->resize(1920, null)->save($cloneFile, type: ImageType::WEBP);
		$upload = $this->createFileUploadFromFile($cloneFile);
		return $forceSquare ? $this->image2square($upload) : $upload;
	}


	/**
	 * @throws ImageException
	 * @throws UnknownImageFileException
	 */
	public function image2jpeg(FileUpload $upload, bool $forceSquare): FileUpload
	{
		$tmpFile = $upload->getTemporaryFile();
		$cloneFile = $this->dir->getTempDir() . '/' . Random::generate(8);
		copy($tmpFile, $cloneFile);
		Image::fromFile($cloneFile)->save($cloneFile, type: ImageType::JPEG);
		$upload = $this->createFileUploadFromFile($cloneFile);
		return $forceSquare ? $this->image2square($upload) : $upload;
	}


	/**
	 * @throws ImageException
	 * @throws UnknownImageFileException
	 */
	public function pdf2jpeg(FileUpload $upload, bool $forceSquare): FileUpload
	{
		$tmpFile = $upload->getTemporaryFile();
		$cloneFile = $this->dir->getTempDir() . '/' . Random::generate(8);
		copy($tmpFile, $cloneFile);
		$img = new \Imagick;
		$img->readImage($cloneFile . '[0]');
		$img->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
		$img->setImageAlphaChannel(\Imagick::ALPHACHANNEL_REMOVE);
		$img->setImageFormat('jpg');
		$img->setImageCompression(\Imagick::COMPRESSION_JPEG);
		$img->setImageCompressionQuality(90);
		$img->setImageUnits(\Imagick::RESOLUTION_PIXELSPERINCH);
		$jpeg = Image::fromString($img->getImageBlob());
		$jpeg->save($cloneFile, type: ImageType::JPEG);
		$upload = $this->createFileUploadFromFile($cloneFile);
		return $forceSquare ? $this->image2square($upload) : $upload;
	}


	/**
	 * @throws ImageException
	 * @throws UnknownImageFileException
	 */
	public function image2square(FileUpload $upload): FileUpload
	{
		$tmpFile = $upload->getTemporaryFile();
		$icon = Image::fromFile($upload->getTemporaryFile());
		$width = $icon->getWidth();
		$height = $icon->getHeight();
		$square = Image::fromBlank(max($width, $height), max($width, $height), ImageColor::rgb(0, 0, 0, 0));
		$top = $width > $height ? round(($width - $height) / 2) : 0;
		$left = $height > $width ? round(($height - $width) / 2) : 0;
		$square->place($icon, (int) $left, (int) $top);
		$square->save($tmpFile, type: ImageType::PNG);
		return $this->createFileUpload($upload);
	}


	public function createFileUpload(FileUpload $upload): FileUpload
	{
		return new FileUpload([
			'name' => $upload->getSanitizedName(),
			'full_path' => $upload->getUntrustedFullPath(),
			'size' => $upload->getSize(),
			'tmp_name' => $upload->getTemporaryFile(),
			'error' => $upload->getError(),
		]);
	}


	public function createFileUploadFromString(string $upload): FileUpload
	{
		$content = base64_decode($upload);
		$name = substr(sha1($content), 0, 8);
		$path = $this->dir->getTempDir() . '/' . $name;
		$size = file_put_contents($path, $content);
		return new FileUpload([
			'name' => $name,
			'full_path' => $path,
			'size' => $size ?: 0,
			'tmp_name' => $path,
			'error' => $size ? UPLOAD_ERR_OK : UPLOAD_ERR_NO_FILE,
		]);
	}


	public function createFileUploadFromFile(string $file): FileUpload
	{
		$size = filesize($file);
		return new FileUpload([
			'name' => basename($file),
			'full_path' => $file,
			'size' => $size ?: 0,
			'tmp_name' => $file,
			'error' => $size ? UPLOAD_ERR_OK : UPLOAD_ERR_NO_FILE,
		]);
	}


	public function createFileUploadFromContent(string $content, ?File $originalFile = null): FileUpload
	{
		$name = $originalFile?->name ?: substr(sha1($content), 0, 8);
		$path = $this->dir->getTempDir() . '/' . $name;
		$size = file_put_contents($path, $content);
		return new FileUpload([
			'name' => $name,
			'full_path' => $path,
			'size' => $size ?: 0,
			'tmp_name' => $path,
			'error' => $size ? UPLOAD_ERR_OK : UPLOAD_ERR_NO_FILE,
		]);
	}
}
