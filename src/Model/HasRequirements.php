<?php

declare(strict_types=1);

namespace Webovac\Core\Model;

use Build\Model\Web\WebData;
use Webovac\Core\Lib\CmsUser;


interface HasRequirements extends ICmsEntity
{
	function checkRequirements(CmsUser $user, WebData $webData, ?string $tag = null): bool;
}
