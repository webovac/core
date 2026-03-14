<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Module;

use Build\Model\Language\LanguageData;
use Build\Model\ModuleTranslation\ModuleTranslation;
use Build\Model\Page\Page;
use Nette\DI\Attributes\Inject;
use Nextras\Orm\Collection\ICollection;
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
}
