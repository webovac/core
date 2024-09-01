<?php

declare(strict_types=1);

namespace Webovac\Core\Definition;

use Stepapo\Utils\Attribute\KeyProperty;
use Stepapo\Utils\Schematic;


class ForeignKey extends Schematic
{
	#[KeyProperty] public string $name;
	public string $table;
	public string $column;
	public string $onDelete = 'cascade';
	public string $onUpdate = 'cascade';
}