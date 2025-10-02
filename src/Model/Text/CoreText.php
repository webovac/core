<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Text;

use Build\Model\Language\LanguageData;
use Build\Model\TextTranslation\TextTranslation;


trait CoreText
{
	public function getTranslation(LanguageData $language): ?TextTranslation
	{
		return $this->translations->toCollection()->getBy(['language' => $language->id]);
	}
}
