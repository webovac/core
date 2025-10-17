<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Text;


trait CoreTextData
{
	public static function createFromArray(mixed $config = [], mixed $key = null, bool $skipDefaults = false, mixed $parentKey = null): static
	{
		$config = isset($config['translations']) ? $config : ['translations' => (array) $config];
		return parent::createFromArray($config, $key, $skipDefaults, $parentKey);
	}
}
