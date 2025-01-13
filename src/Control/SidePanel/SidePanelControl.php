<?php

declare(strict_types=1);

namespace Webovac\Core\Control\SidePanel;

use App\Model\DataModel;
use App\Model\Theme\ThemeData;
use ReflectionException;
use Webovac\Core\Control\BaseControl;
use Webovac\Core\Lib\CmsUser;
use Webovac\Core\Lib\DataProvider;
use Webovac\Core\Lib\ModuleChecker;
use Webovac\Core\Model\CmsEntity;


/**
 * @property SidePanelTemplate $template
 */
class SidePanelControl extends BaseControl
{
	public function __construct(
		private ?CmsEntity $entity,
		private DataModel $dataModel,
		private ModuleChecker $moduleChecker,
		private DataProvider $dataProvider,
		private CmsUser $cmsUser,
	) {}


	/**
	 * @throws ReflectionException
	 */
	public function render(): void
	{
		$webData = $this->dataProvider->getWebData();
		$pageData = $this->dataProvider->getPageData();
		$languageData = $this->dataProvider->getLanguageData();
		$this->template->webData = $webData;
		$this->template->pageData = $pageData;
		$this->template->languageData = $languageData;
		$this->template->dataModel = $this->dataModel;
		$searchModuleData = $this->dataModel->getModuleDataByName('Search');
		$this->template->hasSearch = $this->moduleChecker->isModuleInstalled('search')
			&& $searchModuleData
			&& in_array($searchModuleData->id, $webData->modules, true);
		$personsModuleData = $this->dataModel->getModuleDataByName('Persons');
		$this->template->hasPersons = $this->moduleChecker->isModuleInstalled('persons')
			&& $personsModuleData
			&& in_array($personsModuleData->id, $webData->modules, true);
		if ($this->moduleChecker->isModuleInstalled('style')) {
			$layoutData = $this->dataModel->getLayoutData($webData->layout);
			$this->template->layoutData = $layoutData;
			$this->template->themeDatas = $this->dataModel->themeRepository->findByIds($layoutData->themes);
			$this->template->themeDatas->uasort(fn(ThemeData $a, ThemeData $b) => str_contains('dark', $a->code) !== str_contains('dark', $b->code) ? -1 : 1);
		}
		foreach ($this->dataModel->getPageData($webData->id, $pageData->id)->getCollection('translations') as $translationData) {
			$this->template->availableTranslations[$translationData->language] = $translationData->language;
		}
		$this->template->entity = $this->entity;
		$this->template->isError = $this->presenter->getRequest()->getPresenterName() === 'Error4xx';
		$adminPageData = $this->dataModel->getPageDataByName($this->dataProvider->getWebData()->id, 'Admin:Home');
		$showAdmin =  $adminPageData?->isUserAuthorized($this->cmsUser) ?: false;
		$this->template->showAdmin = $showAdmin;
		if ($showAdmin) {
			$this->template->languageShortcuts = $this->dataModel->languageRepository->findAllPairs();
			$this->template->pageModuleData = $pageData->module ? $this->dataModel->getModuleData($pageData->module) : null;
			$this->template->webDatas = $this->dataModel->getWebDatas();
			$this->template->adminLang = in_array($this->dataProvider->getLanguageData()->id, $adminPageData->getLanguageIds(), true) ? $this->dataProvider->getLanguageData()->shortcut : 'cs';
		}
		$this->template->render(__DIR__ . '/sidePanel.latte');
	}
}
