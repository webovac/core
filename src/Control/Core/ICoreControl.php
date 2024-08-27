<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Core;

use Webovac\Core\Factory;
use Webovac\Core\Model\CmsEntity;


interface ICoreControl extends Factory
{
	function create(
		?CmsEntity $entity = null,
		?array $entityList = null,
	): CoreControl;
}
