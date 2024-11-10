<?php

declare(strict_types=1);

namespace Webovac\Core\Control\SidePanel;

use Stepapo\Utils\Factory;
use Webovac\Core\Model\CmsEntity;


interface ISidePanelControl extends Factory
{
	function create(?CmsEntity $entity): SidePanelControl;
}
