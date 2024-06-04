<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Navigation;

use App\Model\DataModel;
use App\Model\Language\LanguageData;
use App\Model\Page\PageData;
use App\Model\Web\WebData;
use Nette\Application\UI\Multiplier;
use Nextras\Orm\Entity\IEntity;
use Webovac\Core\Control\BaseControl;
use Webovac\Core\Control\MenuItem\IMenuItemControl;
use Webovac\Core\Control\MenuItem\MenuItemControl;
use Webovac\Core\Lib\ModuleChecker;


/**
 * @property NavigationTemplate $template
 */
class NavigationControl extends BaseControl
{
	public function __construct(
		private WebData $webData,
		private ?PageData $pageData,
		private LanguageData $languageData,
		private ?IEntity $entity,
		private DataModel $dataModel,
		private ModuleChecker $moduleChecker,
		private IMenuItemControl $menuItem,
	) {}


	public function render(): void
	{
		if (!$this->pageData) {
			return;
		}
		$this->template->pageDatas = $this->dataModel->getChildPageDatas($this->webData, $this->pageData, $this->languageData);
		$this->template->dataModel = $this->dataModel;
		if ($this->moduleChecker->isModuleInstalled('style')) {
			$this->template->layoutData = $this->dataModel->getLayoutData($this->webData->layout);
		}
		$this->template->languageData = $this->languageData;
		$this->template->entity = $this->entity;
		$this->template->activePageData = $this->pageData;
		$this->template->title = $this->entity && $this->pageData->hasParameter
			? $this->entity->getTitle($this->languageData)
			: $this->pageData->getCollection('translations')->getBy(['language' => $this->languageData->id])->title;
		$this->template->render(__DIR__ . '/navigation.latte');
	}


	public function createComponentActiveMenuItem(): MenuItemControl
	{
		return $this->menuItem->create($this->pageData, $this->webData, $this->languageData, $this->entity, $this->pageData, 'secondary');
	}


	public function createComponentMenuItem(): Multiplier
	{
		return new Multiplier(function ($id): MenuItemControl {
			$pageData = $this->template->pageDatas->getById($this->webData->id . '-' . $id);
			return $this->menuItem->create($pageData, $this->webData, $this->languageData, $this->entity, $this->pageData, 'secondary');
		});
	}
}
