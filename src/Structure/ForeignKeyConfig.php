<?php

namespace Webovac\Core\Structure;


class ForeignKeyConfig extends BaseConfig
{
	public string $name;
	public string $table;
	public string $column;
	public string $onDelete = 'cascade';
	public string $onUpdate = 'cascade';
}