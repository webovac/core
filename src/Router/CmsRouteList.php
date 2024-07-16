<?php

namespace Webovac\Core\Router;

use JetBrains\PhpStorm\Language;
use Nette\Application\Routers\RouteList;


class CmsRouteList extends RouteList
{
	public function addRoute(
		#[Language('TEXT')]
		string $mask,
		array|string|\Closure $metadata = [],
		int|bool $oneWay = 0,
	): static
	{
		$this->add(new CmsRoute($mask, $metadata), (int) $oneWay);
		return $this;
	}
}