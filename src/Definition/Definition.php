<?php

namespace Webovac\Core\Definition;

use Stepapo\Utils\Schematic;


class Definition extends Schematic
{
	/** @var Table[]|array */ public array $tables;


	public static function createFromArray(mixed $config = [], mixed $key = null, bool $skipDefaults = false): static
	{
		$definition = parent::createFromArray($config, $key, $skipDefaults);
		foreach ($definition->tables as $tableKey => $tableConfig) {
			$definition->tables[$tableKey] = Table::createFromArray(
				$tableConfig,
				$tableKey,
				isset($tableConfig['type']) && $tableConfig['type'] === 'alter'
			);
		}
		return $definition;
	}
}