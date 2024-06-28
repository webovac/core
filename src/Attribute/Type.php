<?php

declare(strict_types=1);

namespace Webovac\Core\Attribute;

use Attribute;


#[Attribute(Attribute::TARGET_PROPERTY)]
class Type
{
	public function __construct(public string $class) {}
}