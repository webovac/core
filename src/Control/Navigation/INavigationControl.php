<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Navigation;

use Webovac\Core\Factory;
use Webovac\Core\Model\CmsEntity;


interface INavigationControl extends Factory
{
	function create(?CmsEntity $entity, ?array $entityList): NavigationControl;
}
