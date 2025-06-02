<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Asset;

use Stepapo\Utils\Attribute\KeyProperty;


trait CoreAssetData
{
	public ?int $id;
	#[KeyProperty] public string $name;
}
