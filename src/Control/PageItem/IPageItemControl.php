<?php

declare(strict_types=1);

namespace Webovac\Core\Control\PageItem;

use App\Model\Page\Page;
use Stepapo\Utils\Factory;
use Webovac\Core\Core;


interface IPageItemControl extends Factory
{
	function create(
		Page $page,
		string $moduleClass = Core::class,
		string $templateName = 'default',
	): PageItemControl;
}
