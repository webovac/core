<?php

declare(strict_types=1);

namespace Webovac\Core\Lib;

use Stepapo\Utils\Service;


class PageActivator implements Service
{
	private array $activePages;
	private array $crumbs;


	public function getCrumbs(): array
	{
		return $this->crumbs;
	}


	public function addPage(int $id, string $title, string $link): void
	{
		$this->activePages[$id] = true;
		$this->crumbs[] = [
			'title' => $title,
			'link' => $link,
		];
	}


	public function isActivePage(int $pageId): bool
	{
		return array_key_exists($pageId, $this->activePages);
	}
}