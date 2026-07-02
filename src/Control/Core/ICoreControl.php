<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Core;

use Stepapo\Utils\Factory;
use Webovac\Core\Model\CmsEntity;


interface ICoreControl extends Factory
{
	function create(
		?CmsEntity $entity,
		?array $entityList,
	): CoreControl;
}
