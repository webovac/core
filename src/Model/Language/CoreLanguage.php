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


	public function getIndexDefinition(): IndexDefinition
	{
		$definition = new IndexDefinition;
		$definition->entity = $this;
		$definition->entityName = 'language';
		foreach ($this->translations as $translation) {
			$translationDefinition = new IndexTranslationDefinition;
			$translationDefinition->language = $translation->translationLanguage;
			$translationDefinition->documents = ['A' => $this->name, 'B' => $translation->title];
			$definition->translations[] = $translationDefinition;
		}
		return $definition;
	}


	public function createLog(string $type): ?Log
	{
		$log = new Log;
		$log->language = $this;
		$log->type = $type;
		$log->createdByPerson = match($type) {
			Log::TYPE_CREATE => $this->createdByPerson,
			Log::TYPE_UPDATE => $this->updatedByPerson,
		};
		$log->date = match($type) {
			Log::TYPE_CREATE => $this->createdAt,
			Log::TYPE_UPDATE => $this->updatedAt,
		};
		return $log;
	}
}
