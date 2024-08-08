<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Breadcrumbs;

use App\Model\Web\WebData;
use Webovac\Core\Control\BaseControl;


/**
 * @property BreadcrumbsTemplate $template
 */
class BreadcrumbsControl extends BaseControl
{
	public array $crumbs;


	public function __construct(
		private WebData $webData,
	) {}


	public function render(): void
	{
		$this->template->crumbs = $this->crumbs;
		$this->template->webData = $this->webData;
		$this->template->render(__DIR__ . '/breadcrumbs.latte');
	}


	public function isActivePage(int $pageId): bool
	{
		return array_key_exists($pageId, $this->crumbs);
	}


	public function addCrumb(int $id, string $title, $link): void
	{
		$this->crumbs[$id] = [
			'title' => $title,
			'link' => $link,
		];
	}
}

