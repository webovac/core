<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Menu;

use Stepapo\Utils\Factory;
use Webovac\Core\Model\CmsEntity;


interface IMenuControl extends Factory
{
	function create(?CmsEntity $entity): MenuControl;
}
