<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Buttons;

use Webovac\Core\Factory;
use Webovac\Core\Model\CmsEntity;


interface IButtonsControl extends Factory
{
	function create(?CmsEntity $entity): ButtonsControl;
}
