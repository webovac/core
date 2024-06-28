<?php

namespace Webovac\Core\Definition;

use Nette\InvalidArgumentException;
use Nette\Schema\Processor;
use Nette\Schema\Schema;
use Webovac\Core\Lib\CmsExpect;
use Webovac\Core\Model\CmsDataRepository;

class Schematic
{
	public static function createFromArray(array $config, string $mode = CmsDataRepository::MODE_INSTALL): static
	{
		$schema = static::getSchema($mode);
		if (!$schema) {
			throw new InvalidArgumentException;
		}
		return (new Processor)->process($schema, $config);
	}


	protected static function getSchema(string $mode = CmsDataRepository::MODE_INSTALL): ?Schema
	{
		return CmsExpect::fromDataClass(static::class, $mode);
	}
}