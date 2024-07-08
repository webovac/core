<?php

namespace Webovac\Core\Definition;

use Stepapo\Utils\Attribute\KeyProperty;
use Stepapo\Utils\Schematic;


class Column extends Schematic
{
	#[KeyProperty] public string $name;
	public string $type;
	public bool $null;
	public bool $auto = false;
	public mixed $default = null;
	public ?string $onUpdate = null;
}