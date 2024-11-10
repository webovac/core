<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Breadcrumbs;

use Stepapo\Utils\Factory;


interface IBreadcrumbsControl extends Factory
{
	function create(): BreadcrumbsControl;
}
