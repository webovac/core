<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Web;

use Build\Model\Language\LanguageData;
use Build\Model\Page\Page;
use Build\Model\WebTranslation\WebTranslation;
use Nette\DI\Attributes\Inject;
use Nextras\Orm\Collection\ArrayCollection;
use Nextras\Orm\Collection\ICollection;
use Webovac\Core\Lib\DataProvider;
use Webovac\Core\Model\File\HasFilesTrait;


/**
 * @property string $title {virtual}
 */
trait CoreWeb
{
	use HasFilesTrait;

	public const string DEFAULT_COLOR = '#2196f3';
	public const string DEFAULT_COMPLEMENTARY_COLOR = '#cccccc';
	public const string DEFAULT_ICON_BACKGROUND_COLOR = '#d3eafd';

	#[Inject] public DataProvider $dataProvider;


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
}
