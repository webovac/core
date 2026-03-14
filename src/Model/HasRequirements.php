<?php

declare(strict_types=1);

namespace Webovac\Core\Model;

use Webovac\Core\Lib\CmsUser;


interface HasRequirements
{
	function checkRequirements(CmsUser $user, ?string $tag = null): bool;
}
