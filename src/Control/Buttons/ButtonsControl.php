<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Buttons;

use App\Model\DataModel;
use App\Model\Page\PageData;
use Webovac\Core\Control\BaseControl;
use Webovac\Core\Lib\DataProvider;
use Webovac\Core\Lib\MenuItemRenderer;
use Webovac\Core\Model\CmsEntity;


/**
 * @property ButtonsTemplate $template
 */
class ButtonsControl extends BaseControl
{
	public function __construct(
		private ?CmsEntity $entity,
		private DataModel $dataModel,
		private MenuItemRenderer $menuItemRenderer,
		private DataProvider $dataProvider,
	) {}


	public function render(): void
	{
		$pageData = $this->dataProvider->getButtonsPageData();
		if (!$pageData) {
			return;
		}
		$webData = $this->dataProvider->getWebData();
		$languageData = $this->dataProvider->getLanguageData();
		$this->template->pageData = $pageData;
		$this->template->pageDatas = $this->dataModel->getChildPageDatas($webData, $pageData, $languageData, $this->entity);
		$this->template->webData = $webData;
		$this->template->entity = $this->entity;
		$this->template->addFunction('renderMenuItem', function(PageData $pageData, ?CmsEntity $linkedEntity = null, bool $checkActive = true) use ($webData, $languageData) {
			$this->menuItemRenderer->render('buttons', $this, $webData, $pageData, $languageData, $checkActive, $this->entity, $linkedEntity);
		});
		$this->template->render(__DIR__ . '/buttons.latte');
	}
}
