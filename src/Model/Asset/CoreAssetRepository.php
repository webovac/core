<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Asset;

use Build\Model\Asset\Asset;
use Build\Model\Asset\AssetData;


trait CoreAssetRepository
{
	public function getByData(AssetData|string $data): ?Asset
	{
		return $this->getBy(['link' => $data instanceof AssetData ? $data->link : $data]);
	}
}
