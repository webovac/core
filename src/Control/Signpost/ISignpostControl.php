<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Signpost;

use Stepapo\Utils\Factory;
use Webovac\Core\Model\CmsEntity;
use Webovac\Core\Model\Linkable;


interface ISignpostControl extends Factory
{
	function create(?CmsEntity $entity): SignpostControl;
}
