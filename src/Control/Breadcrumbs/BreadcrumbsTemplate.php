<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Breadcrumbs;

use App\Control\BaseTemplate;
use App\Model\Web\WebData;


class BreadcrumbsTemplate extends BaseTemplate
{
	public array $crumbs;
	public WebData $webData;
}
