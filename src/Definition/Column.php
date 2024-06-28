<?php

namespace Webovac\Core\Definition;

use Webovac\Core\Lib\Schematic;


class Column extends Schematic
{
	public string $name;
	public string $type;
	public bool $null;
	public bool $auto = false;
	public mixed $default = null;
	public ?string $onUpdate = null;
}