<?php

namespace Webovac\Core\Definition;

use Stepapo\Utils\Schematic;
use Webovac\Core\Model\CmsDataRepository;


class Definition extends Schematic
{
	/** @var Table[]|array */ public array $tables;


	public static function createFromArray(array $config, string $mode = CmsDataRepository::MODE_INSTALL): static
	{
		$config = parent::createFromArray($config, $mode);
		foreach ($config->tables as $tableName => $tableConfig) {
			$config->tables[$tableName]['name'] ??= $tableName;
			$config->tables[$tableName] = Table::createFromArray(
				$config->tables[$tableName],
				isset($tableConfig['type']) && $tableConfig['type'] === 'alter' ? CmsDataRepository::MODE_UPDATE : CmsDataRepository::MODE_INSTALL
			);
		}
		return $config;
	}
}