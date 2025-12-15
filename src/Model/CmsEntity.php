<?php

declare(strict_types=1);

namespace Webovac\Core\Model;

use Build\Model\Web\WebData;
use Stepapo\Model\Orm\StepapoEntity;
use Webovac\Core\Lib\CmsUser;


abstract class CmsEntity extends StepapoEntity
{
	public function check(CmsUser $cmsUser, WebData $webData, ?string $tag = null): bool
	{
		if ($this instanceof HasWeb && !$this->checkWeb($webData)) {
			return false;
		}
		if ($this instanceof HasRequirements && !$this->checkRequirements($cmsUser, $tag)) {
			return false;
		}
		return true;
	}
}
