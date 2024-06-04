<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Signpost;

use App\Model\DataModel;
use App\Model\Language\LanguageData;
use App\Model\Page\PageData;
use App\Model\Web\WebData;
use Nette\Application\UI\Multiplier;
use Nextras\Orm\Entity\IEntity;
use Webovac\Core\Control\BaseControl;
use Webovac\Core\Control\MenuItem\IMenuItemControl;
use Webovac\Core\Control\MenuItem\MenuItemControl;
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
		private IMenuItemControl $menuItem,
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


	public function createComponentMenuItem(): Multiplier
	{
		return new Multiplier(function ($id): MenuItemControl {
			$pageData = $this->template->pageDatas->getById($this->webData->id . '-' . $id);
			return $this->menuItem->create($pageData, $this->webData, $this->languageData, $this->entity, $this->pageData, 'content');
		});
	}
}
