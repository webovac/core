<?php

declare(strict_types=1);

namespace Webovac\Core\Lib;

use Nette\Http\FileUpload;
use Stepapo\Utils\Service;


interface FileUploader extends Service
{
	function upload(FileUpload $upload, string $namespace = 'cms'): string;
	function delete(string $identifier): void;
	function getResponse(FileUpload $upload, string $namespace = 'cms'): array;
	function getPath(string $identifier, ?string $size = null, ?string $flag = null, ?int $quality = null): string;
	function getUrl(string $identifier, ?string $size = null, ?string $flag = null, ?int $quality = null): string;
}