<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Module;

use App\Model\Language\LanguageData;
use App\Model\Log\Log;
use App\Model\ModuleTranslation\ModuleTranslation;
use App\Model\Page\Page;
use App\Model\Person\Person;
use App\Model\Web\Web;
use Nextras\Dbal\Utils\DateTimeImmutable;
use Nextras\Orm\Collection\ICollection;
use Nextras\Orm\Relationships\ManyHasMany;
use Nextras\Orm\Relationships\OneHasMany;
use Webovac\Core\IndexDefinition;
use Webovac\Core\IndexTranslationDefinition;


/**
 * @property int $id {primary}
 *
 * @property string $name
 * @property string $title {virtual}
 * @property string $icon
 *
 * @property DateTimeImmutable $createdAt {default now}
 * @property DateTimeImmutable|null $updatedAt
 *
 * @property Page|null $homePage {m:1 Page, oneSided=true}
 * @property Person|null $createdByPerson {m:1 Person, oneSided=true}
 * @property Person|null $updatedByPerson {m:1 Person, oneSided=true}
 *
 * @property OneHasMany|Page[] $pages {1:m Page::$module}
 * @property OneHasMany|ModuleTranslation[] $translations {1:m ModuleTranslation::$module, orderBy=language->rank}
 *
 * @property ManyHasMany|Web[] $webs {m:m Web::$modules, isMain=true}
 */
trait CoreModule
{
	public function getTranslation(LanguageData $language): ?ModuleTranslation
	{
		return $this->translations->toCollection()->getBy(['language' => $language->id]);
	}


	/** @return ICollection<Page> */ 
	public function getPages(): ICollection
	{
		return $this->pages->toCollection()->findBy(['parentPage' => null, 'web' => null]);
	}


	/** @return ICollection<Page> */ 
	public function getPagesForMenu(): ICollection
	{
		return $this->getPages();
	}


	public function getterTitle(): string
	{
		return $this->getTranslation($this->dataProvider->getLanguageData())->title;
	}


	public function getDescription(LanguageData $language): string
	{
		return $this->getTranslation($language)->description;
	}


	public function getIndexDefinition(): IndexDefinition
	{
		$definition = new IndexDefinition;
		$definition->entity = $this;
		$definition->entityName = 'module';
		foreach ($this->translations as $translation) {
			$translationDefinition = new IndexTranslationDefinition;
			$translationDefinition->language = $translation->language;
			$translationDefinition->documents = ['A' => $this->name, 'B' => $translation->title, 'C' => $translation->description];
			$definition->translations[] = $translationDefinition;
		}
		return $definition;
	}


	public function createLog(string $type): ?Log
	{
		$log = new Log;
		$log->module = $this;
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
