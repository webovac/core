<?php

namespace Webovac\Core\Definition;

use Webovac\Core\Lib\Schematic;
use Webovac\Core\Model\CmsData;
use Webovac\Core\Model\CmsDataRepository;


class Manipulation extends Schematic
{
	/** @var CmsData[]|array */ public array $inserts;
	/** @var CmsData[]|array */ public array $updates;


	public static function createFromArray(array $config, string $mode = CmsDataRepository::MODE_INSTALL): static
	{
		$config = parent::createFromArray($config, $mode);
		foreach ($config->inserts as $tableName => $tableConfig) {
			$config->tables[$tableName]['name'] ??= $tableName;
			$config->tables[$tableName] = Table::createFromArray(
				$config->tables[$tableName],
				isset($tableConfig['type']) && $tableConfig['type'] === 'alter' ? CmsDataRepository::MODE_UPDATE : CmsDataRepository::MODE_INSTALL
			);
		}
		return $config;
	}
}