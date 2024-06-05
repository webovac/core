<?php

declare(strict_types=1);

namespace Webovac\Core\Attribute;

use Attribute;


#[Attribute(Attribute::TARGET_PROPERTY)]
class DefaultValue
{
	public function __construct(public mixed $value) {}
}