<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Text;

use App\Model\Language\LanguageData;
use App\Model\TextTranslation\TextTranslation;


trait CoreText
{
	public function getTranslation(LanguageData $language): ?TextTranslation
	{
		return $this->translations->toCollection()->getBy(['language' => $language->id]);
	}
}
