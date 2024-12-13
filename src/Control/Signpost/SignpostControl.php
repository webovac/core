<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Signpost;

use App\Model\DataModel;
use App\Model\Page\PageData;
use Webovac\Core\Control\BaseControl;
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
	) {}


	public function render(): void
	{
		$webData = $this->dataProvider->getWebData();
		$pageData = $this->dataProvider->getPageData();
		$languageData = $this->dataProvider->getLanguageData();
		$this->template->pageDatas = $this->dataModel->getChildPageDatas($webData, $pageData, $languageData, $this->entity);
		$this->template->webData = $webData;
		$this->template->entity = $this->entity;
		$this->template->addFunction('renderMenuItem', function(PageData $pageData, ?CmsEntity $linkedEntity = null) use ($webData, $languageData) {
			$this->menuItemRenderer->render('signpost', $this, $webData, $pageData, $languageData, false, $this->entity, $linkedEntity);
		});
		$this->template->render(__DIR__ . '/signpost.latte');
	}
}
