<?php

namespace Webovac\Core\Definition;


class ForeignKey extends Schematic
{
	public string $name;
	public string $table;
	public string $column;
	public string $onDelete = 'cascade';
	public string $onUpdate = 'cascade';
}