<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Breadcrumbs;

use Build\Control\BaseTemplate;
use Build\Model\Web\WebData;


class BreadcrumbsTemplate extends BaseTemplate
{
	public array $crumbs;
	public WebData $webData;
}
