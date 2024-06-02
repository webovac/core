<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Menu;

use App\Model\DataModel;
use App\Model\Language\LanguageData;
use App\Model\Page\PageData;
use App\Model\Web\WebData;
use Nextras\Orm\Entity\IEntity;
use Webovac\Core\Control\BaseControl;
use Webovac\Core\Lib\Dir;
use Webovac\Core\Lib\FileUploader;
use Webovac\Core\Lib\ModuleChecker;


/**
 * @property MenuTemplate $template
 */
class MenuControl extends BaseControl
{
	public function __construct(
		private WebData $webData,
		private PageData $pageData,
		private LanguageData $languageData,
		private ?IEntity $entity,
		private Dir $dir,
		private DataModel $dataModel,
		private ModuleChecker $moduleChecker,
		private FileUploader $fileUploader,
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
		if ($this->moduleChecker->isModuleInstalled('style')) {
			$this->template->layoutData = $this->dataModel->getLayoutData($this->webData->layout);
		}
		$this->template->entity = $this->entity;
		$this->template->title = $this->webData->getCollection('translations')->getBy(['language' => $this->languageData->id])->title;
		foreach ($this->dataModel->getPageData($this->webData->id, $this->pageData->id)->getCollection('translations') as $translationData) {
			$this->template->availableTranslations[$translationData->language] = $translationData->language;
		}
		$this->template->wwwDir = $this->dir->getWwwDir();
		$this->template->render(__DIR__ . '/menu.latte');
	}


	public function isActive(int $pageId)
	{
		return $this->getParent()->getComponent('breadcrumbs')->isActivePage($pageId);
	}
}
