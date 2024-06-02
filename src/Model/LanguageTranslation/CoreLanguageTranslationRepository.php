<?php

declare(strict_types=1);

namespace Webovac\Core\Model\LanguageTranslation;

use App\Model\Language\Language;
use App\Model\LanguageTranslation\LanguageTranslation;
use App\Model\LanguageTranslation\LanguageTranslationData;


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
