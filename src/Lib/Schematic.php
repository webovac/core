<?php

namespace Webovac\Core\Lib;

use Nette\InvalidArgumentException;
use Nette\Neon\Neon;
use Nette\Schema\Processor;
use Nette\Schema\Schema;
use Nette\Utils\ArrayHash;
use Nette\Utils\FileSystem;
use Stepapo\Dataset\Utils;
use Webovac\Core\Attribute\ArrayOfType;
use Webovac\Core\Attribute\Type;
use Webovac\Core\Model\CmsDataRepository;


class Schematic extends ArrayHash
{
	public static function createFromNeon(string $file, array $params = [], string $mode = CmsDataRepository::MODE_INSTALL): static
	{
		$config = (array) Neon::decode(FileSystem::read($file));
		$parsedConfig = Utils::replaceParams($config, $params);
		return static::createFromArray($parsedConfig, $mode);
	}


	public static function createFromArray(array $config, string $mode = CmsDataRepository::MODE_INSTALL): static
	{
		$schema = static::getSchema($mode);
		if (!$schema) {
			throw new InvalidArgumentException;
		}
		$data = (new Processor)->process($schema, $config);
		$rc = new \ReflectionClass(static::class);
		$props = $rc->getProperties();
		foreach ($props as $prop) {
			$name = $prop->getName();
			if ($attr = $prop->getAttributes(Type::class)) {
				$class = $attr[0]->getArguments()[0];
				if (isset($config[$name])) {
					$data->$name = $class::createFromArray($config[$name], $mode);
				}
			}
			if ($attr = $prop->getAttributes(ArrayOfType::class)) {
				$class = $attr[0]->getArguments()[0];
				$keyProperty = $attr[0]->getArguments()[1] ?? null;
				foreach ($data->$name as $key => $subConfig) {
					if ($keyProperty) {
						$subConfig[$keyProperty] ??= $key;
						unset($data->$name[$key]);
						$key = $subConfig[$keyProperty];
					}
					$data->$name[$key] = $class::createFromArray($subConfig, $mode);
				}
			}
		}
		return $data;
	}


	protected static function getSchema(string $mode = CmsDataRepository::MODE_INSTALL): ?Schema
	{
		return CmsExpect::fromSchematic(static::class, $mode);
	}
}