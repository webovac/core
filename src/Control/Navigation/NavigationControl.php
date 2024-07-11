<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Navigation;

use App\Model\DataModel;
use App\Model\Language\LanguageData;
use App\Model\Page\PageData;
use App\Model\Web\WebData;
use Nette\Application\UI\Multiplier;
use Webovac\Core\Control\BaseControl;
use Webovac\Core\Control\MenuItem\IMenuItemControl;
use Webovac\Core\Control\MenuItem\MenuItemControl;
use Webovac\Core\Lib\ModuleChecker;
use Webovac\Core\Model\CmsEntity;


/**
 * @property NavigationTemplate $template
 */
class NavigationControl extends BaseControl
{
	public function __construct(
		private WebData $webData,
		private ?PageData $pageData,
		private LanguageData $languageData,
		private ?CmsEntity $entity,
		private DataModel $dataModel,
		private ModuleChecker $moduleChecker,
		private IMenuItemControl $menuItem,
	) {}


	/**
	 * @throws \ReflectionException
	 */
	public function render(): void
	{
		if (!$this->pageData) {
			return;
		}
		$this->template->pageDatas = $this->dataModel->getChildPageDatas($this->webData, $this->pageData, $this->languageData);
		if ($this->moduleChecker->isModuleInstalled('style')) {
			$this->template->layoutData = $this->dataModel->getLayoutData($this->webData->layout);
		}
		$this->template->activePageData = $this->pageData;
		$this->template->title = $this->entity && $this->pageData->hasParameter
			? $this->entity->getTitle($this->languageData)
			: $this->pageData->getCollection('translations')->getBy(['language' => $this->languageData->id])->title;
		$this->template->render(__DIR__ . '/navigation.latte');
	}


	public function createComponentActiveMenuItem(): MenuItemControl
	{
		return $this->menuItem->create($this->pageData, $this->webData, $this->languageData, $this->entity, 'secondary', false);
	}


	public function createComponentMenuItem(): Multiplier
	{
		return new Multiplier(function ($id): MenuItemControl {
			$pageData = $this->template->pageDatas->getById($this->webData->id . '-' . $id);
			return $this->menuItem->create($pageData, $this->webData, $this->languageData, $this->entity, 'secondary');
		});
	}
}
