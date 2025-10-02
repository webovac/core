<?php

declare(strict_types=1);

namespace Webovac\Core\Model\FileTranslation;

use Build\Model\File\File;
use Build\Model\FileTranslation\FileTranslation;
use Build\Model\FileTranslation\FileTranslationData;
use Build\Model\Web\WebData;
use Nextras\Orm\Collection\ICollection;


trait CoreFileTranslationRepository
{
	public function getByData(FileTranslationData $data, ?File $file): ?FileTranslation
	{
		if (!$file) {
			return null;
		}
		return $this->getBy(['file' => $file, is_int($data->language) ? 'language->id' : 'language->shortcut' => $data->language]);
	}


	public function getFilterByWeb(WebData $webData): array
	{
		return [
			ICollection::OR,
			'file->page->web' => $webData->id,
			'file->article->web' => $webData->id,
			'file->web' => $webData->id,
		];
	}
}
