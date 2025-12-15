<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Language;

use Build\Model\Language\Language;
use Build\Model\Language\LanguageData;
use Build\Model\Web\WebData;


trait CoreLanguageRepository
{
	public function getKeyParameter(): string
	{
		return 'shortcut';
	}


	public function getByData(LanguageData|string $data): ?Language
	{
		return $this->getBy(['shortcut' => $data instanceof LanguageData ? $data->shortcut : $data]);
	}


	public function getWebFilter(WebData $webData): array
	{
		$ids = [];
		foreach ($webData->translations as $translation) {
			$ids[] = $translation->language;
		}
		return ['id' => $ids];
	}
}
