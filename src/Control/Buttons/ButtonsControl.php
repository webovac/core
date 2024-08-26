<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Buttons;

use App\Model\DataModel;
use App\Model\Language\LanguageData;
use App\Model\Page\PageData;
use App\Model\Web\WebData;
use Webovac\Core\Control\BaseControl;
use Webovac\Core\Control\MenuItem\MenuItemTemplate;
use Webovac\Core\Lib\MenuItemRenderer;
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
		private MenuItemRenderer $menuItemRenderer,
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
		$this->template->entity = $this->entity;
		$this->template->addFunction('renderMenuItem', function(PageData $pageData, ?CmsEntity $linkedEntity = null, bool $checkActive = true) {
			$this->menuItemRenderer->render('buttons', $this, $this->webData, $pageData, $this->languageData, $checkActive, $this->entity, $linkedEntity);
		});
		$this->template->render(__DIR__ . '/buttons.latte');
	}
}
