<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Breadcrumbs;

use App\Model\Web\WebData;
use Webovac\Core\Control\BaseControl;
use Webovac\Core\Lib\DataProvider;


/**
 * @property BreadcrumbsTemplate $template
 */
class BreadcrumbsControl extends BaseControl
{
	public array $crumbs;
	public array $activePages;


	public function __construct(private DataProvider $dataProvider)
	{}


	public function render(): void
	{
		$this->template->crumbs = $this->crumbs;
		$this->template->webData = $this->dataProvider->getWebData();
		$this->template->render(__DIR__ . '/breadcrumbs.latte');
	}


	public function isActivePage(int $pageId): bool
	{
		return array_key_exists($pageId, $this->activePages);
	}


	public function addCrumb(int $id, string $title, $link): void
	{
		$this->activePages[$id] = true;
		$this->crumbs[] = [
			'title' => $title,
			'link' => $link,
		];
	}
}

