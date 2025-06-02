<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Asset;

use App\Model\Asset\Asset;
use App\Model\Asset\AssetData;


trait CoreAssetRepository
{
	public function getByData(AssetData|string $data): ?Asset
	{
		return $this->getBy(['link' => $data instanceof AssetData ? $data->link : $data]);
	}
}
