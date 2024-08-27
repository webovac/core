<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Menu;

use Webovac\Core\Factory;
use Webovac\Core\Model\CmsEntity;


interface IMenuControl extends Factory
{
	function create(?CmsEntity $entity): MenuControl;
}
