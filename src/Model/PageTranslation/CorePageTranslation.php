<?php

declare(strict_types=1);

namespace Webovac\Core\Model\PageTranslation;

use App\Model\Web\Web;


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
}
