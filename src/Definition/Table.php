<?php

namespace Webovac\Core\Definition;

use Stepapo\Utils\Attribute\ArrayOfType;
use Stepapo\Utils\Attribute\KeyProperty;
use Stepapo\Utils\Attribute\Type;
use Stepapo\Utils\Schematic;


class Table extends Schematic
{
	public string $type = 'create';
	#[KeyProperty] public string $name;
	public ?string $schema = null;
	#[ArrayOfType(Column::class)] public array $columns = [];
	#[Type(Key::class)] public Key|array|string|null $primaryKey = null;
	#[ArrayOfType(Key::class)] public array $uniqueKeys = [];
	#[ArrayOfType(Key::class)] public array $indexes = [];
	#[ArrayOfType(ForeignKey::class)] public array $foreignKeys = [];
}