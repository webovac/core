<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Language;

use App\Model\Language\LanguageData;
use App\Model\LanguageTranslation\LanguageTranslation;
use App\Model\Log\Log;
use App\Model\Person\Person;
use Nextras\Dbal\Utils\DateTimeImmutable;
use Nextras\Orm\Relationships\OneHasMany;
use Webovac\Core\IndexDefinition;
use Webovac\Core\IndexTranslationDefinition;


/**
 * @property int $id {primary}
 *
 * @property string $shortcut
 * @property string $name
 * @property string $title {virtual}
 * @property int $rank
 *
 * @property DateTimeImmutable $createdAt {default now}
 * @property DateTimeImmutable|null $updatedAt
 *
 * @property Person|null $createdByPerson {m:1 Person, oneSided=true}
 * @property Person|null $updatedByPerson {m:1 Person, oneSided=true}
 *
 * @property OneHasMany|LanguageTranslation[] $translations {1:m LanguageTranslation::$language, orderBy=translationLanguage->rank}
 */
trait CoreLanguage
{
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
