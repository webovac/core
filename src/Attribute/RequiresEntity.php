<?php

declare(strict_types=1);

namespace Webovac\Core\Attribute;

use Attribute;


#[Attribute(Attribute::TARGET_METHOD)]
class RequiresEntity
{
	public function __construct(public mixed $value)
	{}
}