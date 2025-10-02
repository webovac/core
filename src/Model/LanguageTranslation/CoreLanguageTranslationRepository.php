<?php

declare(strict_types=1);

namespace Webovac\Core\Model\LanguageTranslation;

use Build\Model\Language\Language;
use Build\Model\LanguageTranslation\LanguageTranslation;
use Build\Model\LanguageTranslation\LanguageTranslationData;


trait CoreLanguageTranslationRepository
{
	public function getByData(LanguageTranslationData $data, ?Language $language): ?LanguageTranslation
	{
		if (!$language) {
			return null;
		}
		return $this->getBy(['language' => $language, is_int($data->translationLanguage) ? 'translationLanguage->id' : 'translationLanguage->shortcut' => $data->translationLanguage]);
	}
}
