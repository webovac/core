<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Navigation;

use App\Model\DataModel;
use App\Model\Page\PageData;
use ReflectionException;
use Stepapo\Model\Data\Collection;
use Webovac\Core\Control\BaseControl;
use Webovac\Core\Lib\CmsUser;
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
		private CmsUser $cmsUser,
	) {}


	/**
	 * @throws ReflectionException
	 */
	public function render(): void
	{
		$navigationPageData = $this->dataProvider->getNavigationPageData();
		if (!$navigationPageData) {
			return;
		}
		$webData = $this->dataProvider->getWebData();
		$languageData = $this->dataProvider->getLanguageData();
		$layoutData = $this->dataProvider->getLayoutData();
		if ($this->entityList && method_exists($this->entity, 'getMenuItems')) {
			$this->template->entityMenuItems = $this->entity->getMenuItems();
		}
		$pageDatas = $navigationPageData->getChildPageDatas($this->dataModel, $webData, $this->cmsUser, $this->entity);
		$this->template->pageDatas = $pageDatas;
		$pD = (array) $pageDatas;
		uasort($pD, function (PageData $a, PageData $b) {
			if ($a->hasStyle() === $b->hasStyle()) {
				return $a->rank <=> $b->rank;
			}
			return (int) $b->hasStyle() <=> (int) $a->hasStyle();
		});
		$this->template->mobilePageDatas = new Collection($pD);
		if ($this->moduleChecker->isModuleInstalled('style')) {
			$this->template->layoutData = $layoutData;
		}
		$this->template->activePageData = $navigationPageData;
		$this->template->icon = ($this->entity && $navigationPageData->isDetailRoot && method_exists($this->entity, 'getIcon')
			? $this->entity->getIcon() : null) ?: $navigationPageData->icon;
		$this->template->title = $this->entity && $navigationPageData->hasParameter
			? $this->entity->getTitle()
			: $navigationPageData->getCollection('translations')->getByKey($languageData->id)->title;
		$this->template->imageIdentifier = $this->entity && method_exists($this->entity, 'getImageIdentifier')
			? $this->entity->getImageIdentifier()
			: null;
		$this->template->imageUrl = $this->entity && method_exists($this->entity, 'getImageUrl')
			? $this->entity->getImageUrl()
			: null;
		$this->template->webData = $webData;
		$this->template->dataModel = $this->dataModel;
		$this->template->entity = $this->entity;
		$this->template->addFunction('renderMenuItem', function(PageData $pageData, ?CmsEntity $linkedEntity = null) use ($webData, $languageData, $layoutData, $navigationPageData) {
			$checkActive = $pageData->targetAnchor ? false : ($pageData->targetPage
				? $pageData->targetPage !== $navigationPageData->id
				: $pageData->id !== $navigationPageData->id);
			$this->menuItemRenderer->render('secondary', $this, $webData, $pageData, $layoutData, $languageData, $checkActive, $this->entity, $linkedEntity);
		});
		$this->template->render(__DIR__ . '/navigation.latte');
	}
}
