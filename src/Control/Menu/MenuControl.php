<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Menu;

use App\Model\DataModel;
use App\Model\Page\PageData;
use App\Model\Theme\ThemeData;
use ReflectionException;
use Webovac\Core\Control\BaseControl;
use Webovac\Core\Lib\CmsUser;
use Webovac\Core\Lib\DataProvider;
use Webovac\Core\Lib\Dir;
use Webovac\Core\Lib\FileUploader;
use Webovac\Core\Lib\MenuItemRenderer;
use Webovac\Core\Lib\ModuleChecker;
use Webovac\Core\Lib\PageActivator;
use Webovac\Core\Model\CmsEntity;


/**
 * @property MenuTemplate $template
 */
class MenuControl extends BaseControl
{
	public const TEMPLATE_DEFAULT = 'default';


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
	) {}


	/**
	 * @throws ReflectionException
	 */
	public function render(): void
	{
		$webData = $this->dataProvider->getWebData();
		$layoutData = $this->dataProvider->getLayoutData();
		$pageData = $this->dataProvider->getPageData();
		$languageData = $this->dataProvider->getLanguageData();
		$this->template->webData = $webData;
		if ($webData->logoFile) {
			$this->template->logoUrl = $this->fileUploader->getUrl($webData->logoFile->getDefaultIdentifier());
		}
		$this->template->fileUploader = $this->fileUploader;
		$this->template->pageData = $pageData;
		$this->template->languageData = $languageData;
		$homePage = $this->dataModel->getPageData($webData->id, $webData->homePage);
		$this->template->homeChildPageDatas = $homePage->getChildPageDatas($this->dataModel, $webData, $this->cmsUser, $this->entity);
		$this->template->pageDatas = $webData->getRootPageDatas($this->dataModel, $this->cmsUser, $this->entity);
		$this->template->homePageData = $homePage;
		$this->template->dataModel = $this->dataModel;
		$this->template->webDatas = $this->dataModel->findWebDatas();
		$searchModuleData = $this->dataModel->getModuleDataByName('Search');
		$searchPageData = $this->dataModel->getPageDataByName($this->dataProvider->getWebData()->id, 'Search:Home');
		$this->template->hasSearch = $this->moduleChecker->isModuleInstalled('search')
			&& $searchModuleData
			&& in_array($searchModuleData->id, $webData->modules, true);
		$showSearch = $searchPageData?->isUserAuthorized($this->cmsUser, $webData) ?: false;
		$this->template->showSearch = $showSearch;
		$personsModuleData = $this->dataModel->getModuleDataByName('Persons');
		$this->template->hasPersons = $this->moduleChecker->isModuleInstalled('persons')
			&& $personsModuleData
			&& in_array($personsModuleData->id, $webData->modules, true);
		$adminPageData = $this->dataModel->getPageDataByName($this->dataProvider->getWebData()->id, 'Admin:Home');
		$showAdmin = $adminPageData?->isUserAuthorized($this->cmsUser, $webData) ?: false;
		$this->template->showAdmin = $showAdmin;
		if ($showAdmin) {
			$this->template->languageShortcuts = $this->dataModel->languageRepository->findAllPairs();
			$this->template->pageModuleData = $pageData->module ? $this->dataModel->getModuleData($pageData->module) : null;
			$this->template->webDatas = $this->dataModel->findWebDatas();
			$this->template->adminLang = in_array($this->dataProvider->getLanguageData()->id, $adminPageData->getLanguageIds(), true) ? $this->dataProvider->getLanguageData()->shortcut : 'cs';
		}
		if ($this->moduleChecker->isModuleInstalled('style')) {
			$this->template->layoutData = $layoutData;
			if ($layoutData->hideSidePanel || $layoutData->code === 'cvut') {
				foreach ($this->dataModel->getPageData($webData->id, $pageData->id)->getCollection('translations') as $translationData) {
					$this->template->availableTranslations[$translationData->language] = $translationData->language;
				}
				$this->template->themeDatas = $this->dataModel->findThemeDatas($layoutData->themes);
				$this->template->themeDatas->uasort(fn(ThemeData $a, ThemeData $b) => str_contains('dark', $a->code) !== str_contains('dark', $b->code) ? -1 : 1);
			}
		}
		$this->template->entity = $this->entity;
		$this->template->title = $webData->getCollection('translations')->getByKey($languageData->id)->title;
		$this->template->wwwDir = $this->dir->getWwwDir();
		$this->template->isError = $this->presenter->getRequest()->getPresenterName() === 'Error4xx';
		$this->template->pageActivator = $this->pageActivator;
		$this->template->addFunction('renderMenuItem', function(PageData $pageData, ?CmsEntity $linkedEntity = null) use ($webData, $layoutData, $languageData) {
			$checkActive = $pageData->targetAnchor ? false : ($pageData->targetPage
				? $pageData->targetPage !== $webData->homePage
				: $pageData->id !== $webData->homePage);
			$this->menuItemRenderer->render('primary', $this, $webData, $pageData, $layoutData, $languageData, $checkActive, $this->entity, $linkedEntity);
		});
		$this->template->renderFile($this->moduleClass, MenuControl::class, $this->templateName);
	}
}
