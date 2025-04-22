<?php

declare(strict_types=1);

namespace Webovac\Core\Model\FileTranslation;

use App\Model\File\File;
use App\Model\FileTranslation\FileTranslation;
use App\Model\FileTranslation\FileTranslationData;


trait CoreFileTranslationRepository
{
	public function getByData(FileTranslationData $data, ?File $file): ?FileTranslation
	{
		if (!$file) {
			return null;
		}
		return $this->getBy(['file' => $file, is_int($data->language) ? 'language->id' : 'language->shortcut' => $data->language]);
	}
}
