<?php

declare(strict_types=1);

namespace Webovac\Core\Command;

use Build\Model\File\File;
use Build\Model\Orm;
use Nextras\Dbal\Utils\DateTimeImmutable;
use Stepapo\Utils\Command\Command;
use Tracy\Debugger;
use Webovac\Core\Lib\FileUploader;
use Webovac\Core\Model\File\FileException;
use const PATHINFO_FILENAME, PHP_EOL;


class ProcessFiles implements Command
{
	public function __construct(
		private Orm $orm,
		private FileUploader $fileUploader,
	) {
	}


	public function run(): int
	{
		$error = false;
		$command = $this->orm->commandRepository->getBy(['code' => 'processFiles']);
		$now = new DateTimeImmutable;
		if ($command->running) {
			print 'Command is already running.' . PHP_EOL;
			return 1;
		}
		$command->running = true;
		$command->lastStartAt = new DateTimeImmutable;
		$this->orm->persistAndFlush($command);
		try {
			foreach ($this->orm->fileRepository->findBy(['ready' => false, 'id>' => 8734]) as $file) {
				$this->processFile($file);
			}
		} catch (FileException $e) {
			Debugger::log($e->getMessage(), Debugger::EXCEPTION);
			$error = true;
		}
		$command->running = false;
		$command->lastEndAt = new DateTimeImmutable;
		$this->orm->persistAndFlush($command);
		print ($error ? 'Finished with error' : 'Finished successfully.') . PHP_EOL;
		return $error ? 1 : 0;
	}


	private function processFile(File $file): void
	{
		$path = $this->fileUploader->getPath($file->identifier);
		$upload = $this->orm->fileRepository->createFileUploadFromFile($path);
		if ($upload->getContentType() === 'application/pdf') {
			$namespace = (string) strtok($file->identifier, '/');
			$compatibleUpload = $this->orm->fileRepository->pdf2jpeg($upload);
			if ($compatibleUpload) {
				$modernUpload = $this->orm->fileRepository->image2webp($compatibleUpload);
				$file->compatibleIdentifier = $this->fileUploader->upload($compatibleUpload, $namespace);
				$file->modernIdentifier = $this->fileUploader->upload($modernUpload, $namespace);
			}
			$file->ready = true;
			$this->orm->persistAndFlush($file);
		}
		if (str_contains($upload->getContentType(), 'video/')) {
			$oldIdentifier = $file->identifier;
			$namespace = (string) strtok($oldIdentifier, '/');
			$upload = $this->orm->fileRepository->createVideoUpload($upload);
			$file->extension = 'mp4';
			$file->name = pathinfo($file->name, PATHINFO_FILENAME) . '.mp4';
			$file->contentType = 'video/mp4';
			$compatibleUpload = $this->orm->fileRepository->video2jpg($upload);
			$modernUpload = $this->orm->fileRepository->image2webp($compatibleUpload, maxHeight: 1920);
			$file->capturedAt = $this->orm->fileRepository->getCapturedAt($upload);
			$file->identifier = $this->fileUploader->upload($upload, $namespace);
			$file->compatibleIdentifier = $this->fileUploader->upload($compatibleUpload, $namespace);
			$file->modernIdentifier = $this->fileUploader->upload($modernUpload, $namespace);
			$file->ready = true;
			$this->orm->persistAndFlush($file);
			//$this->fileUploader->delete($oldIdentifier);
		}
	}
}
