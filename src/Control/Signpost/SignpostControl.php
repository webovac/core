<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Signpost;

use App\Model\DataModel;
use App\Model\Language\LanguageData;
use App\Model\Page\PageData;
use App\Model\Web\WebData;
use Webovac\Core\Control\BaseControl;
use Webovac\Core\Control\MenuItem\MenuItemTemplate;
use Webovac\Core\Lib\MenuItemRenderer;
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
		private MenuItemRenderer $menuItemRenderer,
	) {}


	public function render(): void
	{
		$this->template->pageDatas = $this->dataModel->getChildPageDatas($this->webData, $this->pageData, $this->languageData);
		$this->template->webData = $this->webData;
		$this->template->languageData = $this->languageData;
		$this->template->entity = $this->entity;
		$this->template->addFunction('renderMenuItem', function(PageData $pageData, ?CmsEntity $linkedEntity = null) {
			$this->menuItemRenderer->render('signpost', $this, $this->webData, $pageData, $this->languageData, false, $this->entity, $linkedEntity);
		});
		$this->template->render(__DIR__ . '/signpost.latte');
	}
}
