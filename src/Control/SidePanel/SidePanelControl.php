<?php

declare(strict_types=1);

namespace Webovac\Core\Control\SidePanel;

use App\Model\DataModel;
use App\Model\Language\LanguageData;
use App\Model\Page\PageData;
use App\Model\Theme\Theme;
use App\Model\Theme\ThemeData;
use App\Model\Web\WebData;
use ReflectionException;
use Webovac\Core\Control\BaseControl;
use Webovac\Core\Lib\ModuleChecker;
use Webovac\Core\Model\CmsEntity;


/**
 * @property SidePanelTemplate $template
 */
class SidePanelControl extends BaseControl
{
	public function __construct(
		private WebData $webData,
		private PageData $pageData,
		private LanguageData $languageData,
		private ?CmsEntity $entity,
		private DataModel $dataModel,
		private ModuleChecker $moduleChecker,
	) {}


	/**
	 * @throws ReflectionException
	 */
	public function render(): void
	{
		$this->template->webData = $this->webData;
		$this->template->pageData = $this->pageData;
		$this->template->languageData = $this->languageData;
		$this->template->dataModel = $this->dataModel;
		$searchModuleData = $this->dataModel->moduleRepository->getBy(['name' => 'Search']);
		$this->template->hasSearch = $this->moduleChecker->isModuleInstalled('search')
			&& in_array($searchModuleData->id, $this->webData->modules, true);
		$personsModuleData = $this->dataModel->moduleRepository->getBy(['name' => 'Persons']);
		$this->template->hasPersons = $this->moduleChecker->isModuleInstalled('persons')
			&& in_array($personsModuleData->id, $this->webData->modules, true);
		if ($this->moduleChecker->isModuleInstalled('style')) {
			$layoutData = $this->dataModel->getLayoutData($this->webData->layout);
			$this->template->layoutData = $layoutData;
			$this->template->themeDatas = $this->dataModel->themeRepository->findBy(['id' => $layoutData->themes]);
			$this->template->themeDatas->uasort(fn(ThemeData $a, ThemeData $b) => str_contains('dark', $a->code) !== str_contains('dark', $b->code) ? -1 : 1);
		}
		foreach ($this->dataModel->getPageData($this->webData->id, $this->pageData->id)->getCollection('translations') as $translationData) {
			$this->template->availableTranslations[$translationData->language] = $translationData->language;
		}
		$this->template->entity = $this->entity;
		$this->template->isError = $this->presenter->getRequest()->getPresenterName() === 'Error4xx';
		$this->template->render(__DIR__ . '/sidePanel.latte');
	}
}
