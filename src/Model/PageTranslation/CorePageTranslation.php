<?php

declare(strict_types=1);

namespace Webovac\Core\Model\PageTranslation;

use App\Model\Path\Path;
use App\Model\Web\Web;
use Nextras\Orm\Collection\ICollection;


trait CorePageTranslation
{
	public function getRouteMask(?Web $web = null, array $parts = []): string
	{
		$web ??= $this->page->web;
		return '//'
			. $web->host
			. ($web->basePath ? ('/' . $web->basePath) : '')
			. ($parts ? '/' . implode('/', $parts) : '');
	}


	public function getRouteMetadata(?Web $web = null): array
	{
		$web ??= $this->page->web;
		return [
			'presenter' => 'Home',
			'action' => 'default',
			'host' => $web->host,
			'basePath' => $web->basePath,
			'pageName' => $this->page->name,
			'lang' => $this->language->shortcut,
		];
	}


	public function getPaths(Web $web): ICollection
	{
		return $web
			? $this->paths->toCollection()->findBy(['web' => $web])
			: $this->fullPaths->toCollection();
	}


	public function getActivePath(Web $web): ?Path
	{
		return $this->getPaths($web)->getBy(['active' => true]);
	}
}
