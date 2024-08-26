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
use Webovac\Core\Lib\MenuItemRenderer;
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
		private ?array $entityList,
		private DataModel $dataModel,
		private ModuleChecker $moduleChecker,
		private MenuItemRenderer $menuItemRenderer,
	) {}


	/**
	 * @throws ReflectionException
	 */
	public function render(): void
	{
		if (!$this->pageData) {
			return;
		}
		if ($this->entityList && method_exists($this->entity, 'getMenuItems')) {
			$this->template->entityMenuItems = $this->entity->getMenuItems();
		}
		$this->template->pageDatas = $this->dataModel->getChildPageDatas($this->webData, $this->pageData, $this->languageData);
		if ($this->moduleChecker->isModuleInstalled('style')) {
			$this->template->layoutData = $this->dataModel->getLayoutData($this->webData->layout);
		}
		$this->template->activePageData = $this->pageData;
		$this->template->icon = ($this->entity && $this->pageData->isDetailRoot && method_exists($this->entity, 'getIcon') 
			? $this->entity->getIcon() : null) ?: $this->pageData->icon;
		$this->template->title = $this->entity && $this->pageData->hasParameter
			? $this->entity->getTitle($this->languageData)
			: $this->pageData->getCollection('translations')->getBy(['language' => $this->languageData->id])->title;
		$this->template->webData = $this->webData;
		$this->template->languageData = $this->languageData;
		$this->template->dataModel = $this->dataModel;
		$this->template->entity = $this->entity;
		$this->template->addFunction('renderMenuItem', function(PageData $pageData, ?CmsEntity $linkedEntity = null, bool $checkActive = true) {
			$this->menuItemRenderer->render('secondary', $this, $this->webData, $pageData, $this->languageData, $checkActive, $this->entity, $linkedEntity);
		});
		$this->template->render(__DIR__ . '/navigation.latte');
	}
}
