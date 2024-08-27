<?php

declare(strict_types=1);

namespace Webovac\Core\Control\PageItem;

use App\Model\Page\Page;
use Webovac\Core\Core;
use Webovac\Core\Factory;


interface IPageItemControl extends Factory
{
	function create(
		Page $page,
		string $moduleClass = Core::class,
		string $templateName = 'default',
	): PageItemControl;
}
