<?php

namespace Webovac\Core\Control\MenuItem;


use App\Model\DataModel;
use App\Model\Language\LanguageData;
use App\Model\Page\PageData;
use App\Model\Web\WebData;
use Nextras\Orm\Entity\IEntity;
use Webovac\Core\Control\BaseControl;

/**
 * @property MenuItemTemplate $template
 */
class MenuItemControl extends BaseControl
{
	public function __construct(
		private PageData $pageData,
		private WebData $webData,
		private LanguageData $languageData,
		private ?IEntity $entity,
		private ?PageData $activePageData,
		private string $context,
		private DataModel $dataModel,
	) {}


	public function render(): void
	{
		$this->template->pageData = $this->pageData;
		$this->template->pageTranslationData = $this->pageData->getCollection('translations')->getBy(['language' => $this->languageData->id]);
		$this->template->webData = $this->webData;
		$this->template->entity = $this->entity;
		$this->template->activePageData = $this->activePageData;
		$this->template->context = $this->context;
		$this->template->dataModel = $this->dataModel;
		$this->template->render(__DIR__ . '/menuItem.latte');
	}


	public function isActive(int $pageId)
	{
		return $this->presenter->getComponent('core-breadcrumbs')->isActivePage($pageId);
	}
}