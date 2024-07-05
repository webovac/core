<?php

namespace Webovac\Core\Definition;

use Webovac\Core\Attribute\ArrayOfType;
use Webovac\Core\Attribute\Type;
use Webovac\Core\Lib\Schematic;


class Table extends Schematic
{
	public string $type = 'create';
	public string $name;
	public ?string $schema = null;
	#[ArrayOfType(Column::class, 'name')] public array $columns = [];
	#[Type(Key::class)] public Key|array|string|null $primaryKey = null;
	#[ArrayOfType(Key::class)] public array $uniqueKeys = [];
	#[ArrayOfType(Key::class)] public array $indexes = [];
	#[ArrayOfType(ForeignKey::class, 'name')] public array $foreignKeys = [];
}