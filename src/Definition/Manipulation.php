<?php

declare(strict_types=1);

namespace Webovac\Core\Definition;

use Stepapo\Utils\Model\Item;
use Stepapo\Utils\Schematic;


class Manipulation extends Schematic
{
	public string $class;
	public string $type = 'insert';
	public array $modes = ['prod', 'dev', 'test'];
	/** @var Item[]|array */ public array $items;


	public static function createFromArray(mixed $config = [], mixed $key = null, bool $skipDefaults = false, mixed $parentKey = null): static
	{
		$manipulation = parent::createFromArray($config, $key, $skipDefaults);
		foreach ($manipulation->items as $itemKey => $itemConfig) {
			$manipulation->items[$itemKey] = $manipulation->class::createFromArray(
				$itemConfig,
				$itemKey,
				$skipDefaults,
				$parentKey,
			);
		}
		return $manipulation;
	}
}