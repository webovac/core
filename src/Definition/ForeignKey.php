<?php

namespace Webovac\Core\Definition;

use Webovac\Core\Lib\Schematic;


class ForeignKey extends Schematic
{
	public string $name;
	public string $table;
	public string $column;
	public string $onDelete = 'cascade';
	public string $onUpdate = 'cascade';
}