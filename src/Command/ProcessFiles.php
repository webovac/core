<?php

declare(strict_types=1);

namespace Webovac\Core\Command;

use Build\Model\File\File;
use Build\Model\Orm;
use Nextras\Dbal\Utils\DateTimeImmutable;
use Stepapo\Utils\Command\Command;
use Webovac\Core\Lib\FileUploader;


class ProcessFiles implements Command
{
	public function __construct(
		private Orm $orm,
		private FileUploader $fileUploader,
	) {}


	public function run(): int
	{
		$command = $this->orm->commandRepository->getBy(['code' => 'processFiles']);
		$now = new DateTimeImmutable();
		if ($command->running && $command->lastStartAt->modify('+1 hour') > $now) {
			print('Command is already running.' . PHP_EOL);
			return 1;
		}
		$command->running = true;
		$command->lastStartAt = new DateTimeImmutable;
		$this->orm->persistAndFlush($command);
		foreach ($this->orm->fileRepository->findBy(['ready' => false]) as $file) {
			$this->processFile($file);
		}
		$command->running = false;
		$command->lastEndAt = new DateTimeImmutable;
		$this->orm->persistAndFlush($command);
		print('Finished successfully.' . PHP_EOL);
		return 0;
	}


	private function processFile(File $file): void
	{
		$path = $this->fileUploader->getPath($file->identifier);
		$upload = $this->orm->fileRepository->createFileUploadFromFile($path);
		if ($upload->getContentType() === 'application/pdf') {
			$namespace = strtok($file->identifier, '/');
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
			$namespace = strtok($oldIdentifier, '/');
			$upload = $this->orm->fileRepository->createVideoUpload($upload);
			$compatibleUpload = $this->orm->fileRepository->video2jpg($upload);
			$modernUpload = $this->orm->fileRepository->image2webp($compatibleUpload, maxHeight: 1920);
			$file->capturedAt = $this->orm->fileRepository->getCapturedAt($upload);
			$file->identifier = $this->fileUploader->upload($upload, $namespace);
			$file->compatibleIdentifier = $this->fileUploader->upload($compatibleUpload, $namespace);
			$file->modernIdentifier = $this->fileUploader->upload($modernUpload, $namespace);
			$file->ready = true;
			$this->orm->persistAndFlush($file);
			$this->fileUploader->delete($oldIdentifier);
		}
	}
}
