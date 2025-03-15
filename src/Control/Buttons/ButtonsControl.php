<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Buttons;

use App\Model\DataModel;
use App\Model\Page\PageData;
use Webovac\Core\Control\BaseControl;
use Webovac\Core\Lib\CmsUser;
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
		private CmsUser $cmsUser,
	) {}


	public function render(): void
	{
		$buttonsPageData = $this->dataProvider->getButtonsPageData();
		if (!$buttonsPageData) {
			return;
		}
		$webData = $this->dataProvider->getWebData();
		$layoutData = $this->dataProvider->getLayoutData();
		$languageData = $this->dataProvider->getLanguageData();
		$this->template->pageData = $buttonsPageData;
		$this->template->pageDatas = $buttonsPageData->getChildPageDatas($this->dataModel, $webData, $this->cmsUser, $this->entity);
		$this->template->webData = $webData;
		$this->template->entity = $this->entity;
		$this->template->addFunction('renderMenuItem', function(PageData $pageData, ?CmsEntity $linkedEntity = null) use ($webData, $layoutData, $languageData, $buttonsPageData) {
			$checkActive = $pageData->targetPage
				? $pageData->targetPage !== $buttonsPageData->id
				: $pageData->id !== $buttonsPageData->id;
			$this->menuItemRenderer->render('buttons', $this, $webData, $pageData, $layoutData, $languageData, $checkActive, $this->entity, $linkedEntity);
		});
		$this->template->render(__DIR__ . '/buttons.latte');
	}
}
