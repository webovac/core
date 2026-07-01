<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Buttons;

use Stepapo\Utils\Factory;
use Webovac\Core\Model\CmsEntity;
use Webovac\Core\Model\Linkable;


interface IButtonsControl extends Factory
{
	function create(?CmsEntity $entity): ButtonsControl;
}
