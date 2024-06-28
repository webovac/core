<?php

namespace Webovac\Core\Definition;

use Nette\Neon\Neon;
use Nette\Utils\FileSystem;
use Webovac\Core\Model\CmsDataRepository;


class Definition extends Schematic
{
	public string $type;
	/** @var Table[]|array */ public array $tables;


	public static function createFromNeon(string $file): Definition
	{
		$config = (array) Neon::decode(FileSystem::read($file));
		return self::createFromArray($config, $config['type'] === 'create' ? CmsDataRepository::MODE_INSTALL : CmsDataRepository::MODE_UPDATE);
	}


	public static function createFromArray(array $config, string $mode = CmsDataRepository::MODE_INSTALL): static
	{
		$config = parent::createFromArray($config, $mode);
		foreach ($config->tables as $tableName => $tableConfig) {
			$config->tables[$tableName]['name'] ??= $tableName;
			$config->tables[$tableName] = Table::createFromArray($config->tables[$tableName]);
		}
		return $config;
	}
}