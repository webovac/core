<?php

namespace Webovac\Core\Definition;

use Stepapo\Utils\Attribute\ToArray;
use Stepapo\Utils\Attribute\ValueProperty;
use Stepapo\Utils\Schematic;


class Key extends Schematic
{
	#[ValueProperty, ToArray] /** @var string[] */ public array $columns;
}