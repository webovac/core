<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Buttons;

use App\Model\DataModel;
use App\Model\Language\LanguageData;
use App\Model\Page\PageData;
use App\Model\Web\WebData;
use Webovac\Core\Control\BaseControl;
use Webovac\Core\Model\CmsEntity;


/**
 * @property ButtonsTemplate $template
 */
class ButtonsControl extends BaseControl
{
	public function __construct(
		private WebData $webData,
		private ?PageData $pageData,
		private LanguageData $languageData,
		private ?CmsEntity $entity,
		private DataModel $dataModel,
	) {}


	public function render(): void
	{
		if (!$this->pageData) {
			return;
		}
		$this->template->pageData = $this->pageData;
		$this->template->pageDatas = $this->dataModel->getChildPageDatas($this->webData, $this->pageData, $this->languageData);
		$this->template->webData = $this->webData;
		$this->template->languageData = $this->languageData;
		$this->template->dataModel = $this->dataModel;
		$this->template->entity = $this->entity;
		$this->template->render(__DIR__ . '/buttons.latte');
	}
}
