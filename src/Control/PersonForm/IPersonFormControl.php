<?php

declare(strict_types=1);

namespace Webovac\Core\Control\PersonForm;

use Build\Model\Person\Person;
use Stepapo\Utils\Factory;


interface IPersonFormControl extends Factory
{
	function create(Person $person): PersonFormControl;
}
