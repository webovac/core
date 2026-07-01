<?php

declare(strict_types=1);

namespace Webovac\Core\Model;

use Build\Model\Web\WebData;
use Stepapo\Model\Orm\IStepapoEntity;
use Webovac\Core\Lib\CmsUser;


/**
 * @method CmsRepository getRepository()
 */
interface ICmsEntity extends IStepapoEntity
{
	function check(CmsUser $cmsUser, WebData $webData, ?string $tag = null): bool;
}
