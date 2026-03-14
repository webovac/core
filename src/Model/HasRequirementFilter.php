<?php

declare(strict_types=1);

namespace Webovac\Core\Model;

use Webovac\Core\Lib\CmsUser;


interface HasRequirementFilter
{
	function getRequirementFilter(CmsUser $cmsUser): ?array;
}