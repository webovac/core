<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Messages;

use Stepapo\Utils\Factory;


interface IMessagesControl extends Factory
{
	function create(): MessagesControl;
}
