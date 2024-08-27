<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Breadcrumbs;

use App\Model\Web\WebData;
use Webovac\Core\Factory;


interface IBreadcrumbsControl extends Factory
{
	function create(): BreadcrumbsControl;
}
