<?php

namespace Webovac\Core\Structure;

use Webovac\Core\Model\CmsDataRepository;


class TableConfig extends BaseConfig
{
	public string $name;
	/** @var ColumnConfig[]|array */ public array $columns;
	public KeyConfig|array|string $primaryKey;
	/** @var KeyConfig[]|array */ public array $uniqueKeys = [];
	/** @var KeyConfig[]|array */ public array $indexes = [];
	/** @var ForeignKeyConfig[]|array */ public array $foreignKeys = [];


	public static function createFromArray(array $config, string $mode = CmsDataRepository::MODE_INSTALL): static
	{
		$config = parent::createFromArray($config, $mode);
		foreach ($config->columns as $columnName => $columnConfig) {
			$config->columns[$columnName]['name'] = $columnName;
			$config->columns[$columnName] = ColumnConfig::createFromArray($config->columns[$columnName]);
		}
		$config->primaryKey = KeyConfig::createFromArray(['columns' => (array) $config->primaryKey]);
		foreach ($config->uniqueKeys as $key => $value) {
			$config->uniqueKeys[$key] = KeyConfig::createFromArray(['columns' => (array) $value]);
		}
		foreach ($config->indexes as $key => $value) {
			$config->indexes[$key] = KeyConfig::createFromArray(['columns' => (array) $value]);
		}
		foreach ($config->foreignKeys as $fkeyName => $fkeyConfig) {
			$config->foreignKeys[$fkeyName]['name'] ??= $fkeyName;
			$config->foreignKeys[$fkeyName] = ForeignKeyConfig::createFromArray($config->foreignKeys[$fkeyName]);
		}
		return $config;
	}
}