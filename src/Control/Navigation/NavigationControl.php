<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Navigation;

use App\Model\DataModel;
use App\Model\Language\LanguageData;
use App\Model\Page\PageData;
use App\Model\Web\WebData;
use ReflectionException;
use Webovac\Core\Control\BaseControl;
use Webovac\Core\Control\MenuItem\MenuItemTemplate;
use Webovac\Core\Lib\DataProvider;
use Webovac\Core\Lib\MenuItemRenderer;
use Webovac\Core\Lib\ModuleChecker;
use Webovac\Core\Model\CmsEntity;


/**
 * @property NavigationTemplate $template
 */
class NavigationControl extends BaseControl
{
	public function __construct(
		private ?CmsEntity $entity,
		private ?array $entityList,
		private DataModel $dataModel,
		private ModuleChecker $moduleChecker,
		private MenuItemRenderer $menuItemRenderer,
		private DataProvider $dataProvider,
	) {}


	/**
	 * @throws ReflectionException
	 */
	public function render(): void
	{
		$pageData = $this->dataProvider->getNavigationPageData();
		if (!$pageData) {
			return;
		}
		$webData = $this->dataProvider->getWebData();
		$languageData = $this->dataProvider->getLanguageData();
		if ($this->entityList && method_exists($this->entity, 'getMenuItems')) {
			$this->template->entityMenuItems = $this->entity->getMenuItems();
		}
		$this->template->pageDatas = $this->dataModel->getChildPageDatas($webData, $pageData, $languageData);
		if ($this->moduleChecker->isModuleInstalled('style')) {
			$this->template->layoutData = $this->dataProvider->getLayoutData();
		}
		$this->template->activePageData = $pageData;
		$this->template->icon = ($this->entity && $pageData->isDetailRoot && method_exists($this->entity, 'getIcon')
			? $this->entity->getIcon() : null) ?: $pageData->icon;
		$this->template->title = $this->entity && $pageData->hasParameter
			? $this->entity->getTitle($languageData)
			: $pageData->getCollection('translations')->getBy(['language' => $languageData->id])->title;
		$this->template->webData = $webData;
		$this->template->languageData = $languageData;
		$this->template->dataModel = $this->dataModel;
		$this->template->entity = $this->entity;
		$this->template->addFunction('renderMenuItem', function(PageData $pageData, ?CmsEntity $linkedEntity = null, bool $checkActive = true) use ($webData, $languageData) {
			$this->menuItemRenderer->render('secondary', $this, $webData, $pageData, $languageData, $checkActive, $this->entity, $linkedEntity);
		});
		$this->template->render(__DIR__ . '/navigation.latte');
	}
}
