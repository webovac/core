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
		$this->template->pageDatas = $webData->getRootPageDatas($this->dataModel, $this->cmsUser, $this->entity);
		$this->template->homePageData = $this->dataModel->getPageData($webData->id, $webData->homePage);
		$this->template->dataModel = $this->dataModel;
		$searchModuleData = $this->dataModel->getModuleDataByName('Search');
		$this->template->hasSearch = $this->moduleChecker->isModuleInstalled('search')
			&& $searchModuleData
			&& in_array($searchModuleData->id, $webData->modules, true);
		if ($this->moduleChecker->isModuleInstalled('style')) {
			$this->template->layoutData = $layoutData;
			if ($layoutData->hideSidePanel) {
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
		$this->template->addFunction('renderMenuItem', function(PageData $pageData, ?CmsEntity $linkedEntity = null) use ($webData, $layoutData, $languageData) {
			$checkActive = $pageData->targetPage
				? $pageData->targetPage !== $webData->homePage
				: $pageData->id !== $webData->homePage;
			$this->menuItemRenderer->render('primary', $this, $webData, $pageData, $layoutData, $languageData, $checkActive, $this->entity, $linkedEntity);
		});
		$this->template->renderFile($this->moduleClass, MenuControl::class, $this->templateName);
	}
}
