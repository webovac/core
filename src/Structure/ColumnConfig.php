<?php

namespace Webovac\Core\Structure;


class ColumnConfig extends BaseConfig
{
	public string $name;
	public string $type;
	public bool $null;
	public bool $auto = false;
	public mixed $default = null;
	public ?string $onUpdate = null;
}