<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Lib;

use Build\Model\Asset\AssetData;
use Stepapo\Utils\Attribute\ArrayOfType;
use Stepapo\Utils\Attribute\KeyProperty;


trait CoreLibData
{
	public ?int $id;
	#[KeyProperty] public string $name;
	/** @var AssetData[] */ #[ArrayOfType(AssetData::class)] public array|null $assets;
}
