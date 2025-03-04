<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Module;

use App\Model\Language\LanguageData;
use App\Model\Log\Log;
use App\Model\ModuleTranslation\ModuleTranslation;
use App\Model\Page\Page;
use Nette\DI\Attributes\Inject;
use Nextras\Orm\Collection\ICollection;
use Webovac\Core\IndexDefinition;
use Webovac\Core\IndexTranslationDefinition;
use Webovac\Core\Lib\DataProvider;


/**
 * @property string $title {virtual}
 */
trait CoreModule
{
	#[Inject] public DataProvider $dataProvider;


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


	public function getDescription(): ?string
	{
		return $this->getTranslation($this->dataProvider->getLanguageData())?->description;
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
