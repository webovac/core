<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Language;


trait CoreLanguageDataRepository
{
	public function findAllPairs(): array
	{
		$return = [];
		foreach ($this->findAll() as $languageData) {
			$return[$languageData->id] = $languageData->shortcut;
		}
		return $return;
	}
}