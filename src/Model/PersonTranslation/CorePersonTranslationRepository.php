<?php

declare(strict_types=1);

namespace Webovac\Core\Model\PersonTranslation;

use Build\Model\Person\Person;
use Build\Model\PersonTranslation\PersonTranslation;
use Build\Model\PersonTranslation\PersonTranslationData;


trait CorePersonTranslationRepository
{
	public function getByData(PersonTranslationData $data, ?Person $person): ?PersonTranslation
	{
		if (!$person) {
			return null;
		}
		return $this->getBy(['person' => $person, is_int($data->language) ? 'language->id' : 'language->shortcut' => $data->language]);
	}
}
