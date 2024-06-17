<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Menu;

use App\Model\DataModel;
use App\Model\Language\LanguageData;
use App\Model\Page\PageData;
use App\Model\Web\WebData;
use Nette\Application\UI\Multiplier;
use Webovac\Core\Control\BaseControl;
use Webovac\Core\Control\MenuItem\IMenuItemControl;
use Webovac\Core\Control\MenuItem\MenuItemControl;
use Webovac\Core\Lib\Dir;
use Webovac\Core\Lib\FileUploader;
use Webovac\Core\Lib\ModuleChecker;
use Webovac\Core\Model\CmsEntity;


/**
 * @property MenuTemplate $template
 */
class MenuControl extends BaseControl
{
	public function __construct(
		private WebData $webData,
		private PageData $pageData,
		private LanguageData $languageData,
		private ?CmsEntity $entity,
		private Dir $dir,
		private DataModel $dataModel,
		private ModuleChecker $moduleChecker,
		private FileUploader $fileUploader,
		private IMenuItemControl $menuItem,
	) {}


	public function render(): void
	{
		$this->template->webData = $this->webData;
		if ($this->webData->logoFile) {
			$this->template->logoUrl = $this->fileUploader->getUrl($this->webData->logoFile->getDefaultIdentifier());
		}
		$this->template->pageData = $this->pageData;
		$this->template->pageDatas = $this->dataModel->getRootPageDatas($this->webData, $this->languageData);
		$this->template->languageData = $this->languageData;
		$this->template->homePageData = $this->dataModel->getHomePageData($this->webData->id);
		$this->template->dataModel = $this->dataModel;
		$searchModuleData = $this->dataModel->moduleRepository->getBy(['name' => 'Search']);
		$this->template->hasSearch = $this->moduleChecker->isModuleInstalled('search')
			&& in_array($searchModuleData->id, $this->webData->modules, true);
		$this->template->hasAuth = $this->moduleChecker->isModuleInstalled('search')
			&& in_array($searchModuleData->id, $this->webData->modules, true);
		if ($this->moduleChecker->isModuleInstalled('style')) {
			$this->template->layoutData = $this->dataModel->getLayoutData($this->webData->layout);
		}
		$this->template->entity = $this->entity;
		$this->template->title = $this->webData->getCollection('translations')->getBy(['language' => $this->languageData->id])->title;
		foreach ($this->dataModel->getPageData($this->webData->id, $this->pageData->id)->getCollection('translations') as $translationData) {
			$this->template->availableTranslations[$translationData->language] = $translationData->language;
		}
		$this->template->wwwDir = $this->dir->getWwwDir();
		$this->template->isError = $this->presenter->getRequest()->getPresenterName() === 'Error4xx';
		$this->template->render(__DIR__ . '/menu.latte');
	}


	public function createComponentMenuItem(): Multiplier
	{
		return new Multiplier(function ($id): MenuItemControl {
			$pageData = $this->template->pageDatas->getById($this->webData->id . '-' . $id);
			return $this->menuItem->create($pageData, $this->webData, $this->languageData, $this->entity, $this->pageData, 'primary', $this->webData->homePage !== $pageData->id);
		});
	}
}
