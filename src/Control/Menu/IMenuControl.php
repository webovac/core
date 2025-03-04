<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Menu;

use Stepapo\Utils\Factory;
use Webovac\Core\Core;
use Webovac\Core\Model\CmsEntity;


interface IMenuControl extends Factory
{
	function create(
		?CmsEntity $entity,
		string $moduleClass = Core::class,
		string $templateName = MenuControl::TEMPLATE_DEFAULT
	): MenuControl;
}
