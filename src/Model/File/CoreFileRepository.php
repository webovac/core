<?php

declare(strict_types=1);

namespace Webovac\Core\Model\File;

use App\Model\File\File;
use App\Model\File\FileRepository;
use App\Model\Person\Person;
use Choowx\RasterizeSvg\Svg;
use Nette\Http\FileUpload;
use Nette\Utils\Image;
use Nette\Utils\ImageColor;
use Nette\Utils\ImageException;
use Nette\Utils\ImageType;
use Nette\Utils\Random;
use Nette\Utils\UnknownImageFileException;
use Nextras\Orm\Entity\Entity;


trait CoreFileRepository
{
	public function createFile(FileUpload|string|null $upload, ?Person $person = null, bool $forceSquare = false): ?File
	{
		if (!$upload) {
			return null;
		}
		if (is_string($upload)) {
			$upload = $this->createFileUploadFromString($upload);
		}
		if (!$upload->hasFile()) {
			return null;
		}
		$identifier = $this->fileUploader->upload($upload);
		$exists = $this->getModel()->getRepository(FileRepository::class)->getBy(['identifier' => $identifier]);
		$file = $exists ?: new File;
		$file->name = $upload->getSanitizedName();
		$file->extension = $upload->getSuggestedExtension();
		if (!$exists) {
			$file->identifier = $identifier;
			$file->contentType = $upload->getContentType();
			$file->type = $upload->getContentType() === 'image/svg+xml' ? File::TYPE_SVG : ($upload->isImage() ? File::TYPE_IMAGE : File::TYPE_FILE);
			if ($upload->getContentType() === 'image/svg+xml') {
				$compatibleUpload = $this->svg2png($upload, $forceSquare);
				$file->compatibleIdentifier = $this->fileUploader->upload($compatibleUpload);
				$modernUpload = $this->image2webp($compatibleUpload, $forceSquare);
				$file->modernIdentifier = $this->fileUploader->upload($modernUpload);
			} elseif ($upload->getContentType() === 'image/webp' || $upload->getContentType() === 'image/avif') {
				$compatibleUpload = $this->image2jpeg($upload, $forceSquare);
				$file->compatibleIdentifier = $this->fileUploader->upload($compatibleUpload);
				$file->modernIdentifier = $identifier;
			} elseif ($upload->isImage()) {
				$modernUpload = $this->image2webp($upload, $forceSquare);
				$file->compatibleIdentifier = $identifier;
				$file->modernIdentifier = $this->fileUploader->upload($modernUpload);
			}
		} else {
			$file->createdByPerson = $person;
		}
		$this->persist($file);
		return $file;
	}


	public function svg2png(FileUpload $upload, bool $forceSquare): FileUpload
	{
		$tmpFile = $upload->getTemporaryFile();
		$cloneFile = $this->dir->getTempDir() . '/' . Random::generate(8);
		copy($tmpFile, $cloneFile);
		Svg::make(file_get_contents($cloneFile))->saveAsPng($cloneFile);
		$upload = $this->createFileUploadFromString(base64_encode(file_get_contents($cloneFile)));
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
		Image::fromFile($cloneFile)->save($cloneFile, type: ImageType::WEBP);
		$upload = $this->createFileUploadFromString(base64_encode(file_get_contents($cloneFile)));
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
		$upload = $this->createFileUploadFromString(base64_encode(file_get_contents($cloneFile)));
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
//		$name = Random::generate(8);
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


	public function createFileUploadFromContent(string $content, ?File $originalFile = null): FileUpload
	{
//		$name = $originalFile?->name ?: Random::generate(8);
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
