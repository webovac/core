<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Buttons;

use App\Model\Language\LanguageData;
use App\Model\Page\PageData;
use App\Model\Web\WebData;
use Nette\Application\UI\Multiplier;
use Webovac\Core\Control\BaseControl;
use Webovac\Core\Control\MenuItem\IMenuItemControl;
use Webovac\Core\Control\MenuItem\MenuItemControl;
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
		private IMenuItemControl $menuItem,
	) {}


	public function render(): void
	{
		if (!$this->pageData) {
			return;
		}
		$this->template->pageDatas = $this->dataModel->getChildPageDatas($this->webData, $this->pageData, $this->languageData);
		$this->template->render(__DIR__ . '/buttons.latte');
	}


	public function createComponentActiveMenuItem(): MenuItemControl
	{
		return $this->menuItem->create($this->pageData, $this->webData, $this->languageData, $this->entity, 'buttons', false);
	}


	public function createComponentMenuItem(): Multiplier
	{
		return new Multiplier(function ($id): MenuItemControl {
			$pageData = $this->template->pageDatas->getById($this->webData->id . '-' . $id);
			return $this->menuItem->create($pageData, $this->webData, $this->languageData, $this->entity, 'buttons');
		});
	}
}
