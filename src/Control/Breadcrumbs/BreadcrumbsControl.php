<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Breadcrumbs;

use Webovac\Core\Control\BaseControl;
use Webovac\Core\Lib\DataProvider;
use Webovac\Core\Lib\PageActivator;


/**
 * @property BreadcrumbsTemplate $template
 */
class BreadcrumbsControl extends BaseControl
{
	public array $crumbs;


	public function __construct(
		private DataProvider $dataProvider,
		private PageActivator $pageActivator,
	) {}


	public function render(): void
	{
		$this->template->crumbs = $this->pageActivator->getCrumbs();
		$this->template->webData = $this->dataProvider->getWebData();
		$this->template->render(__DIR__ . '/breadcrumbs.latte');
	}
}

