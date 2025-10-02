<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Signpost;

use Build\Model\DataModel;
use Build\Model\Page\PageData;
use Webovac\Core\Control\BaseControl;
use Webovac\Core\Lib\CmsUser;
use Webovac\Core\Lib\DataProvider;
use Webovac\Core\Lib\MenuItemRenderer;
use Webovac\Core\Model\CmsEntity;


/**
 * @property SignpostTemplate $template
 */
class SignpostControl extends BaseControl
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
		$webData = $this->dataProvider->getWebData();
		$layoutData = $this->dataProvider->getLayoutData();
		$pageData = $this->dataProvider->getPageData();
		$languageData = $this->dataProvider->getLanguageData();
		$this->template->pageDatas = $pageData->getChildPageDatas($this->dataModel, $webData, $this->cmsUser, $this->entity);
		$this->template->webData = $webData;
		$this->template->entity = $this->entity;
		$this->template->pageData = $pageData;
		$this->template->addFunction('renderMenuItem', function(PageData $pageData, ?CmsEntity $linkedEntity = null) use ($webData, $layoutData, $languageData) {
			$this->menuItemRenderer->render('signpost', $this, $webData, $pageData, $layoutData, $languageData, false, $this->entity, $linkedEntity);
		});
		$this->template->render(__DIR__ . '/signpost.latte');
	}
}
