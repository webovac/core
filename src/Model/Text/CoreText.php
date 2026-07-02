<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Text;

use Build\Model\Language\LanguageData;
use Build\Model\TextTranslation\TextTranslation;
use Stepapo\Model\Orm\AuditableTrait;


trait CoreText
{
	use AuditableTrait;

	public function getTranslation(LanguageData $language): ?TextTranslation
	{
		return $this->translations->toCollection()->getBy(['language' => $language->id]);
	}
}
