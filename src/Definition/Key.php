<?php

namespace Webovac\Core\Definition;

use Stepapo\Utils\Attribute\ToArray;
use Stepapo\Utils\Attribute\ValueProperty;
use Stepapo\Utils\Schematic;


class Key extends Schematic
{
	/** @var string[] */ #[ValueProperty, ToArray] public array $columns;
}