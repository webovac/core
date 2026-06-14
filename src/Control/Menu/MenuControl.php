<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Menu;

use Build\Model\DataModel;
use Build\Model\Language\LanguageData;
use Build\Model\Layout\LayoutData;
use Build\Model\Page\Page;
use Build\Model\Page\PageData;
use Build\Model\Theme\ThemeData;
use Build\Model\Web\WebData;
use Nette\Utils\Arrays;
use ReflectionException;
use Webovac\Core\Control\BaseControl;
use Webovac\Core\Lib\CmsUser;
use Webovac\Core\Lib\DataProvider;
use Webovac\Core\Lib\Dir;
use Webovac\Core\Lib\FileUploader;
use Webovac\Core\Lib\MenuItemRenderer;
use Webovac\Core\Lib\ModuleChecker;
use Webovac\Core\Lib\PageActivator;
use Webovac\Core\Lib\PageRequirementChecker;
use Webovac\Core\Model\CmsEntity;


/**
 * @property MenuTemplate $template
 */
class MenuControl extends BaseControl
{
	public const TEMPLATE_DEFAULT = 'default';
	private WebData $webData;
	private LanguageData $languageData;
	private LayoutData $layoutData;


	public function __construct(
		private string $moduleClass,
		private string $templateName,
		private ?CmsEntity $entity,
		private Dir $dir,
		private DataModel $dataModel,
		private ModuleChecker $moduleChecker,
		private FileUploader $fileUploader,
		private MenuItemRenderer $menuItemRenderer,
		private DataProvider $dataProvider,
		private CmsUser $cmsUser,
		private PageActivator $pageActivator,
		private PageRequirementChecker $requirementChecker,
	) {}


	/**
	 * @throws ReflectionException
	 */
	public function render(): void
	{
		$menuPageData = $this->dataProvider->getMenuPageData();
		$this->webData = $this->dataProvider->getWebData();
		$this->layoutData = $this->dataProvider->getLayoutData();
		$pageData = $this->dataProvider->getPageData();
		$this->languageData = $this->dataProvider->getLanguageData();
		$this->template->webData = $this->webData;
		if ($this->webData->logoFile) {
			$this->template->logoUrl = $this->fileUploader->getUrl($this->webData->logoFile->getDefaultIdentifier());
		}
		$this->template->fileUploader = $this->fileUploader;
		$this->template->pageData = $pageData;
		$this->template->languageData = $this->languageData;
		$this->template->defaultLanguageData = $this->dataModel->getLanguageData($this->webData->defaultLanguage);
		$this->template->menuPageData = $menuPageData;
		if ($menuPageData) {
			$pageDatas = $menuPageData->getChildPageDatas($this->dataModel, $this->webData, $this->cmsUser);
			$this->template->homePageData = $menuPageData;
		} else {
			$pageDatas = $this->webData->getRootPageDatas($this->dataModel, $this->cmsUser);
			$this->template->homePageData = $this->dataModel->getPageData($this->webData->id, $this->webData->homePage);
		}
		$this->template->pageDatas = $this->requirementChecker->filterPages($pageDatas, $this->entity);
		$this->template->dataModel = $this->dataModel;
		$this->template->webDatas = $this->dataModel->findWebDatas();
		$searchModuleData = $this->dataModel->getModuleDataByName('Search');
		$searchPageData = $this->dataModel->getPageDataByName($this->dataProvider->getWebData()->id, 'Search:Home');
		$this->template->hasSearch = $this->moduleChecker->isModuleInstalled('search')
			&& $searchModuleData
			&& in_array($searchModuleData->id, $this->webData->modules, true);
		$showSearch = $searchPageData && $this->requirementChecker->isPageAccessible($searchPageData);
		$this->template->showSearch = $showSearch;
		$personsModuleData = $this->dataModel->getModuleDataByName('Persons');
		$this->template->hasPersons = $this->moduleChecker->isModuleInstalled('persons')
			&& $personsModuleData
			&& in_array($personsModuleData->id, $this->webData->modules, true);
		$adminPageData = $this->dataModel->getPageDataByName($this->dataProvider->getWebData()->id, 'Admin:Home');
		$showAdmin = $adminPageData && $this->requirementChecker->isPageAccessible($adminPageData);
		$this->template->showAdmin = $showAdmin;
		if ($showAdmin) {
			$this->template->languageShortcuts = $this->dataModel->languageDataRepository->findAllPairs();
			$this->template->pageModuleData = $pageData->module ? $this->dataModel->getModuleData($pageData->module) : null;
			$this->template->webDatas = $this->dataModel->findWebDatas();
			$this->template->adminLang = in_array($this->dataProvider->getLanguageData()->id, $adminPageData->getLanguageIds(), true) ? $this->dataProvider->getLanguageData()->shortcut : 'cs';
		}
		if ($this->moduleChecker->isModuleInstalled('style')) {
			$this->template->layoutData = $this->layoutData;
			if ($this->layoutData->hideSidePanel || $this->layoutData->code === 'cvut') {
				foreach ($this->dataModel->getPageData($this->webData->id, $pageData->id)->getCollection('translations') as $translationData) {
					$this->template->availableTranslations[$translationData->language] = $translationData->language;
				}
				$this->template->themeDatas = $this->dataModel->findThemeDatas($this->layoutData->themes);
				$this->template->themeDatas->uasort(fn(ThemeData $a, ThemeData $b) => str_contains('dark', $a->code) !== str_contains('dark', $b->code) ? -1 : 1);
			}
		}
		$this->template->entity = $this->entity;
		$this->template->title = $this->webData->getCollection('translations')->getByKey($this->languageData->id)->title;
		$this->template->wwwDir = $this->dir->getWwwDir();
		$this->template->isError = $this->presenter->getRequest()->getPresenterName() === 'Core:Error4xx';
		$this->template->pageActivator = $this->pageActivator;
		$this->template->requirementChecker = $this->requirementChecker;
		$this->template->addFunction('renderMenuItem', function(PageData $pageData, ?CmsEntity $linkedEntity = null) {
			$checkActive = !$pageData->targetAnchor && ($pageData->targetPage
				? $pageData->targetPage !== $this->webData->homePage
				: $pageData->id !== $this->webData->homePage);
			$this->menuItemRenderer->render('primary', $this, $this->webData, $pageData, $this->layoutData, $this->languageData, $checkActive, $this->entity, $linkedEntity);
		});
		$this->template->renderFile($this->moduleClass, MenuControl::class, $this->templateName);
	}


	public function getHref(PageData $pageData, ?CmsEntity $linkedEntity = null): ?string
	{
		$e = $linkedEntity ?: $this->entity;
		$anchor = null;
		if ($pageData->type === Page::TYPE_INTERNAL_LINK && $pageData->targetPage) {
			$p = $this->dataModel->getPageData($pageData->webData->id, $pageData->targetPage);
			$path = $pageData->targetPath;
			$parameter = $pageData->targetParameter ? [$e->getPageName() => $pageData->targetParameter] : null;
			$anchor = $pageData->targetAnchor;
		} else {
			$p = $pageData;
			$parameter = $p->hasParameter && !isset($this->presenter->path) ? $e?->getParameters() : null;
			$path = $p->hasPath && isset($this->presenter->path) ? ($this->presenter->path . '/' . Arrays::first($e->getParameters())) : '';
		}
		return match($p->type) {
			Page::TYPE_SIGNAL => $this->presenter->getName() === 'Core:Error4xx' ? null : $this->presenter->link('//' . $p->targetSignal . '!'),
			Page::TYPE_EXTERNAL_LINK => $p->targetUrl,
			Page::TYPE_PAGE => $this->presenter->link(
				'//Home:' . ($anchor ? '#' . $anchor : ''),
				[
					'pageName' => $p->name,
					'lang' => $this->languageData->shortcut,
					'id' => $parameter,
					'path' => $path,
				],
			),
			default => null,
		};
	}


	public function getClass(PageData $pageData, bool $checkActive, ?CmsEntity $linkedEntity = null): string
	{
		# TODO fix targetPage
		return 'menu-item' . ($pageData->style ? ' btn btn-subtle-' . $pageData->style : '')
			. ((!$pageData->targetPath && !$pageData->targetAnchor && ($pageData->id === $this->presenter->pageData->id || $pageData->targetPage === $this->presenter->pageData->id) && (!$linkedEntity || $linkedEntity === $this->entity))
			|| ($checkActive && $this->isActive($pageData, $linkedEntity, $pageData->targetPath))
			|| ($checkActive && $pageData->targetPage && $this->isActive($pageData, $linkedEntity, $this->targetPath)) ? ' active' : '')
			;
	}


	public function setModuleClass(string $moduleClass): self
	{
		$this->moduleClass = $moduleClass;
		return $this;
	}


	private function isActive(PageData $pageData, ?CmsEntity $linkedEntity, ?string $path = null)
	{
		if ($linkedEntity && $linkedEntity !== $pageData->entity) {
			return false;
		}
		return (!$path || str_contains($this->presenter->path ?? '', $path)) && $this->pageActivator->isActivePage($pageData->targetPage ?: $pageData->id);
	}
}
