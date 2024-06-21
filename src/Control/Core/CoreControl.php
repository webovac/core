<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Core;

use App\Model\Language\LanguageData;
use App\Model\Page\PageData;
use App\Model\Web\WebData;
use Webovac\Core\Command\MigrateAndInstallCommand;
use Webovac\Core\Control\BaseControl;
use Webovac\Core\Control\Breadcrumbs\BreadcrumbsControl;
use Webovac\Core\Control\Breadcrumbs\IBreadcrumbsControl;
use Webovac\Core\Control\Buttons\ButtonsControl;
use Webovac\Core\Control\Buttons\IButtonsControl;
use Webovac\Core\Control\Menu\IMenuControl;
use Webovac\Core\Control\Menu\MenuControl;
use Webovac\Core\Control\Navigation\INavigationControl;
use Webovac\Core\Control\Navigation\NavigationControl;
use Webovac\Core\Control\SidePanel\ISidePanelControl;
use Webovac\Core\Control\SidePanel\SidePanelControl;
use Webovac\Core\Control\Signpost\ISignpostControl;
use Webovac\Core\Control\Signpost\SignpostControl;
use Webovac\Core\MainModuleControl;
use Webovac\Core\Model\CmsEntity;


/**
 * @property CoreTemplate $template
 */
class CoreControl extends BaseControl implements MainModuleControl
{
	public function __construct(
		private WebData $webData,
		private LanguageData $languageData,
		private ?PageData $pageData,
		private ?PageData $navigationPageData,
		private ?PageData $buttonsPageData,
		private ?CmsEntity $entity,
		private ?CmsEntity $parentEntity,
		private IMenuControl $menu,
		private INavigationControl $navigation,
		private IButtonsControl $buttons,
		private ISignpostControl $signpost,
		private ISidePanelControl $sidePanel,
		private IBreadcrumbsControl $breadcrumbs,
		private MigrateAndInstallCommand $command,
	) {}


	public function render(): void
	{
		$this->template->render(__DIR__ . '/core.latte');
	}


	public function handleReset(): void
	{
		$_SERVER['argv'][] = 'a';
		$_SERVER['argv'][] = '--reset';
		$this->command->run();
		ob_clean();
		$this->presenter->flashMessage('Obnoveno', 'success');
		$this->presenter->redirect('this');
	}


	public function createComponentMenu(): MenuControl
	{
		return $this->menu->create($this->webData, $this->pageData, $this->languageData, $this->entity);
	}


	public function createComponentNavigation(): NavigationControl
	{
		return $this->navigation->create($this->webData, $this->navigationPageData, $this->languageData, $this->entity);
	}


	public function createComponentButtons(): ButtonsControl
	{
		return $this->buttons->create($this->webData, $this->buttonsPageData, $this->languageData, $this->entity);
	}


	public function createComponentSignpost(): SignpostControl
	{
		return $this->signpost->create($this->webData, $this->pageData, $this->languageData, $this->entity);
	}


	public function createComponentSidePanel(): SidePanelControl
	{
		return $this->sidePanel->create($this->webData, $this->pageData, $this->languageData, $this->entity);
	}


	public function createComponentBreadcrumbs(): BreadcrumbsControl
	{
		return $this->breadcrumbs->create($this->webData, $this->pageData, $this->languageData, $this->entity, $this->parentEntity);
	}
}
