<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Language;

use Build\Model\Language\LanguageData;
use Build\Model\LanguageTranslation\LanguageTranslation;
use Build\Model\Log\Log;
use Nette\DI\Attributes\Inject;
use Webovac\Core\IndexDefinition;
use Webovac\Core\IndexTranslationDefinition;
use Webovac\Core\Lib\DataProvider;


/**
 * @property string $title {virtual}
 */
trait CoreLanguage
{
	#[Inject] public DataProvider $dataProvider;


	public function getTranslation(LanguageData $language): ?LanguageTranslation
	{
		return $this->translations->toCollection()->getBy(['translationLanguage' => $language->id]);
	}


	public function getterTitle(): string
	{
		return $this->getTranslation($this->dataProvider->getLanguageData())->title;
	}
}
