<?php

declare(strict_types=1);

namespace Webovac\Core\Model\File;

use Build\Model\Article\Article;
use Build\Model\File\File;
use Build\Model\File\FileData;
use Build\Model\File\FileRepository;
use Build\Model\Page\Page;
use Build\Model\Web\Web;
use Build\Model\Web\WebData;
use Choowx\RasterizeSvg\Svg;
use Nette\Http\FileUpload;
use Nette\InvalidArgumentException;
use Nette\Utils\Image;
use Nette\Utils\ImageColor;
use Nette\Utils\ImageException;
use Nette\Utils\ImageType;
use Nette\Utils\Json;
use Nette\Utils\Process;
use Nette\Utils\ProcessFailedException;
use Nette\Utils\Random;
use Nette\Utils\UnknownImageFileException;
use Nextras\Dbal\Utils\DateTimeImmutable;
use Nextras\Orm\Collection\ICollection;
use Tracy\Debugger;
use Webovac\Core\Model\CmsEntity;


trait CoreFileRepository
{
	public function getByData(FileData $data, ?CmsEntity $entity = null): ?File
	{
		if ($data instanceof FileData) {
			if (!isset($data->identifier)) {
				return null;
			}
			return $this->getBy([
				'identifier' => $data->identifier,
				'web' => $entity instanceof Web ? $entity : null,
				'article' => $entity instanceof Article ? $entity : null,
				'page' => $entity instanceof Page ? $entity : null,
			]);
		}
		return $this->getBy(['identifier' => $data]);
	}


	public function createFileData(FileData $data, ?string $key = null, string $namespace = 'cms'): ?FileData
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
		$name = pathinfo($data->upload->getSanitizedName(), PATHINFO_FILENAME);
		if ($data->upload->isImage()) {
			$exif = @exif_read_data($data->upload->getTemporaryFile());
			$data->capturedAt = isset($exif['DateTimeOriginal']) ? DateTimeImmutable::createFromFormat('Y:m:d H:i:s', $exif['DateTimeOriginal']) : null;
			$this->rotateImage($data->upload);
		}
		if (str_contains($data->upload->getContentType(), 'video/')) {
//			$data->upload = $this->createVideoUpload($data->upload);
//			$data->capturedAt = $this->getCapturedAt($data->upload);
		}
		$identifier = $this->fileUploader->upload($data->upload, $namespace);
		$file = $this->getModel()->getRepository(FileRepository::class)->getBy(['identifier' => $identifier]);
		$extension = pathinfo($data->upload->getSanitizedName(), PATHINFO_EXTENSION) ?: $data->upload->getSuggestedExtension();
		$data->name = "$name.$extension";
		$data->extension = $extension;
		if (!$file) {
			$data->identifier = $identifier;
			$data->contentType = $data->upload->getContentType();
			$data->type = $data->upload->getContentType() === 'image/svg+xml' ? File::TYPE_SVG : (
			$data->upload->isImage()
				? File::TYPE_IMAGE
				: (str_contains($data->upload->getContentType(), 'video/') ? File::TYPE_VIDEO : File::TYPE_FILE)
			);
			if ($data->upload->getContentType() === 'image/svg+xml') {
				$compatibleUpload = $this->svg2png($data->upload, $data->forceSquare);
				$image = Image::fromFile($compatibleUpload->getTemporaryFile());
				$data->compatibleIdentifier = $this->fileUploader->upload($compatibleUpload, $namespace);
				$modernUpload = $this->image2webp($compatibleUpload, $data->forceSquare, 1920);
				$data->modernIdentifier = $this->fileUploader->upload($modernUpload, $namespace);
				$data->width = $image->getWidth();
				$data->height = $image->getHeight();
			} elseif ($data->upload->getContentType() === 'image/webp' || $data->upload->getContentType() === 'image/avif') {
				$compatibleUpload = $this->image2jpeg($data->upload, $data->forceSquare);
				$data->compatibleIdentifier = $this->fileUploader->upload($compatibleUpload, $namespace);
				$data->modernIdentifier = $identifier;
			} elseif ($data->upload->isImage()) {
				$compatibleUpload = $data->upload;
				$modernUpload = $this->image2webp($data->upload, $data->forceSquare, 1920);
				$data->compatibleIdentifier = $identifier;
				$data->modernIdentifier = $this->fileUploader->upload($modernUpload, $namespace);
			} elseif ($data->upload->getContentType() === 'application/pdf') {
//				$compatibleUpload = $this->pdf2jpeg($data->upload, $data->forceSquare);
//				if ($compatibleUpload) {
//					$data->compatibleIdentifier = $this->fileUploader->upload($compatibleUpload, $namespace);
//					$modernUpload = $this->image2webp($compatibleUpload, $data->forceSquare);
//					$data->modernIdentifier = $this->fileUploader->upload($modernUpload, $namespace);
//				}
			} elseif (str_contains($data->upload->getContentType(), 'video/')) {
//				$compatibleUpload = $this->video2jpg($data->upload, $data->forceSquare);
//				$data->compatibleIdentifier = $this->fileUploader->upload($compatibleUpload, $namespace);
//				$modernUpload = $this->image2webp($compatibleUpload, $data->forceSquare, 1920);
//				$data->modernIdentifier = $this->fileUploader->upload($modernUpload, $namespace);
			}
		} else {
			$data->identifier = $file->identifier;
			$data->contentType = $file->contentType;
			$data->type = $file->type;
			$data->compatibleIdentifier = $file->compatibleIdentifier;
			$data->modernIdentifier = $file->modernIdentifier;
			$data->width = $file->width;
			$data->height = $file->height;
		}
		if (isset($compatibleUpload)) {
			$file = $compatibleUpload->getTemporaryFile();
			$image = Image::fromFile($file);
			$data->width = $image->getWidth();
			$data->height = $image->getHeight();
		}
		if ($data->upload->getContentType() !== 'application/pdf' && !str_contains($data->upload->getContentType(), 'video/')) {
			$data->ready = true;
		}
		return $data;
	}


	public function rotateImage(FileUpload $upload): void
	{
		if (!$upload->isImage()) {
			throw new InvalidArgumentException('File is not image.');
		}
		$file = $upload->getTemporaryFile();
		$image = Image::fromFile($file);
		$exif = @exif_read_data($file);
		if (isset($exif['Orientation'])) {
			switch ($exif['Orientation']) {
				case 8:
					$image->rotate(90, 0);
					break;
				case 3:
					$image->rotate(180, 0);
					break;
				case 6:
					$image->rotate(-90, 0);
					break;
			}
		}
		$image->save($file, type: Image::detectTypeFromFile($file));
	}


	public function createVideoUpload(FileUpload $upload): FileUpload
	{
		$inputFile = $upload->getTemporaryFile();
		$dir = pathinfo($inputFile, PATHINFO_DIRNAME);
		$name = Random::generate(8);
		$outputFile = "$dir/$name.mp4";
		try {
			$process = Process::runExecutable('systemd-run', [
				'--scope',
				'-p', 'CPUQuota=100%',
				'ffmpeg',
				'-y',
				'-i', $inputFile,
				'-vcodec', 'libx265',
				'-b:v', '2000k',
				'-preset', 'medium',
				'-vtag', 'hvc1',
				'-vf', 'scale=1920:-2,setsar=1',
				'-pix_fmt', 'yuv420p',
				'-acodec', 'aac',
				'-b:a', '224k',
				'-map_metadata', '0',
				'-movflags', '+faststart',
				$outputFile,
			], timeout: null);
			$process->ensureSuccess();
		} catch (ProcessFailedException $e) {
			Debugger::log([$process->getStdOutput(), $process->getStdError()], Debugger::EXCEPTION);
			throw new FileException('Process failed.');
		}
		
		return $this->createFileUploadFromFile($outputFile);
	}


	public function getCapturedAt(FileUpload $upload): ?DateTimeImmutable
	{
		$file = $upload->getTemporaryFile();
		$proc = "ffprobe -v quiet %s -print_format json -show_entries format_tags=creation_time";
		$process = Process::runExecutable('ffprobe', [
			'-v', 'quiet',
			$file,
			'-print_format', 'json',
			'-show_entries', 'format_tags=creation_time',
		], timeout: null);
		$result = Json::decode($process->getStdOutput());
		return isset($result->format->tags->creation_time)
			? new DateTimeImmutable($result->format->tags->creation_time)
			: null;
	}


	public function svg2png(FileUpload $upload, bool $forceSquare = false): FileUpload
	{
		$tmpFile = $upload->getTemporaryFile();
		$cloneFile = $this->dir->getTempDir() . '/' . Random::generate(8);
		copy($tmpFile, $cloneFile);
		Svg::make(file_get_contents($cloneFile))->saveAsPng($cloneFile);
		$upload = $this->createFileUploadFromFile($cloneFile);
		return $forceSquare ? $this->image2square($upload) : $upload;
	}


	public function video2jpg(FileUpload $upload, bool $forceSquare = false): FileUpload
	{
		$inputFile = $upload->getTemporaryFile();
		$dir = pathinfo($inputFile, PATHINFO_DIRNAME);
		$filename = pathinfo($inputFile, PATHINFO_FILENAME);
		$outputFile = "$dir/$filename.jpg";
		$command = "ffmpeg -i %s -vcodec mjpeg -vframes 1 -an -f rawvideo -ss `ffmpeg -i %s 2>&1 | grep Duration | awk '{print $2}' | tr -d , | awk -F ':' '{print ($3+$2*60+$1*3600)/2}'` %s";
		Process::runCommand(sprintf($command, $inputFile, $inputFile, $outputFile))->ensureSuccess();
		$upload = $this->createFileUploadFromFile($outputFile);
		return $forceSquare ? $this->image2square($upload) : $upload;
	}


	/**
	 * @throws ImageException
	 * @throws UnknownImageFileException
	 */
	public function image2webp(FileUpload $upload, bool $forceSquare = false, ?int $maxHeight = null): FileUpload
	{
		$tmpFile = $upload->getTemporaryFile();
		$cloneFile = $this->dir->getTempDir() . '/' . Random::generate(8);
		copy($tmpFile, $cloneFile);
		Image::fromFile($cloneFile)->resize(1920, $maxHeight, Image::ShrinkOnly)->save($cloneFile, type: ImageType::WEBP);
		$upload = $this->createFileUploadFromFile($cloneFile);
		return $forceSquare ? $this->image2square($upload) : $upload;
	}


	/**
	 * @throws ImageException
	 * @throws UnknownImageFileException
	 */
	public function image2jpeg(FileUpload $upload, bool $forceSquare = false): FileUpload
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
	public function pdf2jpeg(FileUpload $upload, bool $forceSquare = false): ?FileUpload
	{
		try {
			$tmpFile = $upload->getTemporaryFile();
			$cloneFile = $this->dir->getTempDir() . '/' . Random::generate(8);
			copy($tmpFile, $cloneFile);
			$img = new \Imagick;
			$img->setColorspace(\Imagick::COLORSPACE_SRGB);
			$img->readImage($cloneFile . '[0]');
			$img->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
			$img->setImageAlphaChannel(\Imagick::ALPHACHANNEL_REMOVE);
			$img->setImageFormat('jpg');
			$img->setImageCompression(\Imagick::COMPRESSION_JPEG);
			$img->setImageCompressionQuality(90);
			$img->setImageUnits(\Imagick::RESOLUTION_PIXELSPERINCH);
			$jpeg = Image::fromString($img->getImageBlob());
			$jpeg->save($cloneFile, type: ImageType::JPEG);
			$img->clear();
			$upload = $this->createFileUploadFromFile($cloneFile);
			return $forceSquare ? $this->image2square($upload) : $upload;
		} catch (\ImagickException $e) {
			Debugger::log($e->getMessage());
			return null;
		}
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
		$size = @filesize($file);
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


	public function getWebFilter(WebData $webData): array
	{
		return [
			ICollection::OR,
			'page->web' => $webData->id,
			'article->web' => $webData->id,
			'web' => $webData->id,
		];
	}
}
