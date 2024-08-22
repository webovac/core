<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Menu;

use App\Model\DataModel;
use App\Model\Language\LanguageData;
use App\Model\Page\PageData;
use App\Model\Theme\ThemeData;
use App\Model\Web\WebData;
use Latte\Engine;
use Nette\Application\LinkGenerator;
use ReflectionException;
use Webovac\Core\Control\BaseControl;
use Webovac\Core\Control\MenuItem\MenuItemTemplate;
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
		private LinkGenerator $linkGenerator,
	) {}


	/**
	 * @throws ReflectionException
	 */
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
		$this->template->linkGenerator = $this->linkGenerator;
		$searchModuleData = $this->dataModel->moduleRepository->getBy(['name' => 'Search']);
		$this->template->hasSearch = $this->moduleChecker->isModuleInstalled('search')
			&& $searchModuleData
			&& in_array($searchModuleData->id, $this->webData->modules, true);
		if ($this->moduleChecker->isModuleInstalled('style')) {
			$layoutData = $this->dataModel->getLayoutData($this->webData->layout);
			$this->template->layoutData = $layoutData;
			if ($layoutData->hideSidePanel) {
				foreach ($this->dataModel->getPageData($this->webData->id, $this->pageData->id)->getCollection('translations') as $translationData) {
					$this->template->availableTranslations[$translationData->language] = $translationData->language;
				}
				$this->template->themeDatas = $this->dataModel->themeRepository->findBy(['id' => $layoutData->themes]);
				$this->template->themeDatas->uasort(fn(ThemeData $a, ThemeData $b) => str_contains('dark', $a->code) !== str_contains('dark', $b->code) ? -1 : 1);
			}
		}
		$this->template->entity = $this->entity;
		$this->template->title = $this->webData->getCollection('translations')->getBy(['language' => $this->languageData->id])->title;
		$this->template->wwwDir = $this->dir->getWwwDir();
		$this->template->isError = $this->presenter->getRequest()->getPresenterName() === 'Error4xx';
		$this->template->render(__DIR__ . '/menu.latte');
	}
}
