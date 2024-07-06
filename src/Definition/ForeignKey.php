<?php

namespace Webovac\Core\Definition;

use Stepapo\Utils\Schematic;


class ForeignKey extends Schematic
{
	public string $name;
	public string $table;
	public string $column;
	public string $onDelete = 'cascade';
	public string $onUpdate = 'cascade';
}