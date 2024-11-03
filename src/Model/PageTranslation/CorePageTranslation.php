<?php

declare(strict_types=1);

namespace Webovac\Core\Model\PageTranslation;

use App\Model\Language\Language;
use App\Model\Page\Page;
use App\Model\Person\Person;
use App\Model\Web\Web;
use Nextras\Dbal\Utils\DateTimeImmutable;


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
