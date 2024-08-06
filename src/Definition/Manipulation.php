<?php

namespace Webovac\Core\Definition;

use Stepapo\Utils\Model\Item;
use Stepapo\Utils\Schematic;


class Manipulation extends Schematic
{
	public string $class;
	public string $type = 'insert';
	public bool $dev = false;
	public bool $test = false;
	/** @var Item[]|array */ public array $items;


	public static function createFromArray(mixed $config = [], mixed $key = null, bool $skipDefaults = false): static
	{
		$manipulation = parent::createFromArray($config, $key, $skipDefaults);
		foreach ($manipulation->items as $itemKey => $itemConfig) {
			$manipulation->items[$itemKey] = $manipulation->class::createFromArray(
				$itemConfig,
				$itemKey,
				isset($config['type']) && $config['type'] === 'update'
			);
		}
		return $manipulation;
	}
}