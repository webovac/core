<?php

namespace Webovac\Core\Definition;

use Stepapo\Utils\Schematic;
use Webovac\Core\Model\CmsDataRepository;


class Key extends Schematic
{
	/** @var string[] */ public array $columns;


	public static function createFromArray(array|string $config, string $mode = CmsDataRepository::MODE_INSTALL): static
	{
		$config = isset($config['columns']) ? $config : ['columns' => (array) $config];
		return parent::createFromArray($config, $mode);
	}
}