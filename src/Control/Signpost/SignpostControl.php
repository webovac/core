<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Signpost;

use App\Model\DataModel;
use App\Model\Language\LanguageData;
use App\Model\Page\PageData;
use App\Model\Web\WebData;
use Nextras\Orm\Entity\IEntity;
use Webovac\Core\Control\BaseControl;
use Webovac\Core\Lib\ModuleChecker;


/**
 * @property SignpostTemplate $template
 */
class SignpostControl extends BaseControl
{
	public function __construct(
		private WebData $webData,
		private PageData $pageData,
		private LanguageData $languageData,
		private ?IEntity $entity,
		private DataModel $dataModel,
		private ModuleChecker $moduleChecker,
	) {}


	public function render(): void
	{
		$this->template->pageData = $this->pageData;
		$this->template->pageDatas = $this->dataModel->getChildPageDatas($this->webData, $this->pageData, $this->languageData);
		$this->template->dataModel = $this->dataModel;
		$this->template->languageData = $this->languageData;
		if ($this->moduleChecker->isModuleInstalled('style')) {
			$this->template->layoutData = $this->dataModel->getLayoutData($this->webData->layout);
		}
		$this->template->entity = $this->entity;
		$this->template->render(__DIR__ . '/signpost.latte');
	}
}
