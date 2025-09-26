<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Core;

use Webovac\Core\Command\MigrateCommand;
use Webovac\Core\Control\BaseControl;
use Webovac\Core\Control\Breadcrumbs\BreadcrumbsControl;
use Webovac\Core\Control\Breadcrumbs\IBreadcrumbsControl;
use Webovac\Core\Control\Buttons\ButtonsControl;
use Webovac\Core\Control\Buttons\IButtonsControl;
use Webovac\Core\Control\Documents\DocumentsControl;
use Webovac\Core\Control\Documents\IDocumentsControl;
use Webovac\Core\Control\Gallery\GalleryControl;
use Webovac\Core\Control\Gallery\IGalleryControl;
use Webovac\Core\Control\Menu\IMenuControl;
use Webovac\Core\Control\Menu\MenuControl;
use Webovac\Core\Control\Messages\IMessagesControl;
use Webovac\Core\Control\Messages\MessagesControl;
use Webovac\Core\Control\Navigation\INavigationControl;
use Webovac\Core\Control\Navigation\NavigationControl;
use Webovac\Core\Control\SidePanel\ISidePanelControl;
use Webovac\Core\Control\SidePanel\SidePanelControl;
use Webovac\Core\Control\Signpost\ISignpostControl;
use Webovac\Core\Control\Signpost\SignpostControl;
use Webovac\Core\MainModuleControl;
use Webovac\Core\Model\CmsEntity;


class CoreControl extends BaseControl implements MainModuleControl
{
	public function __construct(
		private ?CmsEntity $entity,
		private ?array $entityList,
		private IMenuControl $menu,
		private INavigationControl $navigation,
		private IMessagesControl $message,
		private IButtonsControl $buttons,
		private ISignpostControl $signpost,
		private ISidePanelControl $sidePanel,
		private IBreadcrumbsControl $breadcrumbs,
		private IGalleryControl $gallery,
		private IDocumentsControl $documents,
	) {}


	public function createComponentMenu(): MenuControl
	{
		return $this->menu->create($this->entity);
	}


	public function createComponentNavigation(): NavigationControl
	{
		return $this->navigation->create($this->entity, $this->entityList);
	}


	public function createComponentButtons(): ButtonsControl
	{
		return $this->buttons->create($this->entity);
	}


	public function createComponentSignpost(): SignpostControl
	{
		return $this->signpost->create($this->entity);
	}


	public function createComponentSidePanel(): SidePanelControl
	{
		return $this->sidePanel->create($this->entity);
	}


	public function createComponentBreadcrumbs(): BreadcrumbsControl
	{
		return $this->breadcrumbs->create();
	}


	public function createComponentMessages(): MessagesControl
	{
		return $this->message->create();
	}


	public function createComponentGallery(): GalleryControl
	{
		return $this->gallery->create($this->entity);
	}


	public function createComponentDocuments(): DocumentsControl
	{
		return $this->documents->create($this->entity);
	}
}
