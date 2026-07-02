<?php

declare(strict_types=1);

namespace Webovac\Core\Control\SidePanel;

use Stepapo\Utils\Factory;
use Webovac\Core\Core;
use Webovac\Core\Model\CmsEntity;


interface ISidePanelControl extends Factory
{
	function create(
		?CmsEntity $entity,
		string $moduleClass = Core::class,
		string $templateName = SidePanelControl::TEMPLATE_DEFAULT,
	): SidePanelControl;
}
