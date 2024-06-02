<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Breadcrumbs;

use App\Control\BaseTemplate;
use App\Model\Web\Web;


class BreadcrumbsTemplate extends BaseTemplate
{
	public array $crumbs;
	public Web $web;
}
