<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Signpost;

use App\Model\DataModel;
use App\Model\Language\LanguageData;
use App\Model\Page\PageData;
use App\Model\Web\WebData;
use Webovac\Core\Control\BaseControl;
use Webovac\Core\Model\CmsEntity;


/**
 * @property SignpostTemplate $template
 */
class SignpostControl extends BaseControl
{
	public function __construct(
		private WebData $webData,
		private PageData $pageData,
		private LanguageData $languageData,
		private ?CmsEntity $entity,
		private DataModel $dataModel,
	) {}


	public function render(): void
	{
		$this->template->pageDatas = $this->dataModel->getChildPageDatas($this->webData, $this->pageData, $this->languageData);
		$this->template->webData = $this->webData;
		$this->template->languageData = $this->languageData;
		$this->template->dataModel = $this->dataModel;
		$this->template->entity = $this->entity;
		$this->template->render(__DIR__ . '/signpost.latte');
	}
}
