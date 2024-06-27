<?php

namespace Webovac\Core\Structure;

use Nette\Neon\Neon;
use Nette\Utils\FileSystem;
use Webovac\Core\Model\CmsDataRepository;


class StructureConfig extends BaseConfig
{
	public string $type;
	/** @var TableConfig[]|array */ public array $tables;


	public static function createFromNeon(string $file): StructureConfig
	{
		$config = (array) Neon::decode(FileSystem::read($file));
		return self::createFromArray($config, $config['type'] === 'create' ? CmsDataRepository::MODE_INSTALL : CmsDataRepository::MODE_UPDATE);
	}


	public static function createFromArray(array $config, string $mode = CmsDataRepository::MODE_INSTALL): static
	{
		$config = parent::createFromArray($config, $mode);
		foreach ($config->tables as $tableName => $tableConfig) {
			$config->tables[$tableName]['name'] ??= $tableName;
			$config->tables[$tableName] = TableConfig::createFromArray($config->tables[$tableName]);
		}
		return $config;
	}
}