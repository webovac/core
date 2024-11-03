<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Web;

use App\Model\File\File;
use App\Model\Language\Language;
use App\Model\Language\LanguageData;
use App\Model\Log\Log;
use App\Model\Module\Module;
use App\Model\Page\Page;
use App\Model\Person\Person;
use App\Model\Preference\Preference;
use App\Model\WebTranslation\WebTranslation;
use Nextras\Dbal\Utils\DateTimeImmutable;
use Nextras\Orm\Collection\ArrayCollection;
use Nextras\Orm\Collection\ICollection;
use Nextras\Orm\Relationships\ManyHasMany;
use Nextras\Orm\Relationships\OneHasMany;
use Webovac\Core\IndexDefinition;
use Webovac\Core\IndexTranslationDefinition;


/**
 * @property string $title {virtual}
 */
trait CoreWeb
{
	public const string DEFAULT_COLOR = '#2196f3';
	public const string DEFAULT_COMPLEMENTARY_COLOR = '#cccccc';
	public const string DEFAULT_ICON_BACKGROUND_COLOR = '#d3eafd';


	public function getterTitle(): string
	{
		return $this->getTranslation($this->dataProvider->getLanguageData())->title;
	}


	public function getTranslation(LanguageData $language): ?WebTranslation
	{
		return $this->translations->toCollection()->getBy(['language' => $language->id]);
	}


	/** @return Page[]&ICollection */ 
	public function getPages(): ICollection
	{
		return $this->pages->toCollection()->findBy(['parentPage' => null]);
	}


	/** @return Page[]&ICollection */ 
	public function getPagesForMenu(): ICollection
	{
		$pages = [];
		/** @var Page $page */
		foreach ($this->pages->toCollection()->findBy(['parentPage' => null]) as $page) {
			if ($page->type === Page::TYPE_MODULE) {
				foreach ($page->targetModule->getPages() as $modulePage) {
					$pages[] = $modulePage;
				}
			} else {
				$pages[] = $page;
			}
		}
		return new ArrayCollection($pages, $this->getRepository());
	}


	public function getMenuItems(): array
	{
		return $this->getRepository()->findBy(['id!=' => $this->id])->fetchPairs('id');
	}


	public function getIndexDefinition(): IndexDefinition
	{
		$definition = new IndexDefinition;
		$definition->entity = $this;
		$definition->entityName = 'web';
		foreach ($this->translations as $translation) {
			$translationDefinition = new IndexTranslationDefinition;
			$translationDefinition->language = $translation->language;
			$translationDefinition->documents = ['A' => $this->code, 'B' => $translation->title];
			$definition->translations[] = $translationDefinition;
		}
		return $definition;
	}


	public function createLog(string $type): ?Log
	{
		$log = new Log;
		$log->web = $this;
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
