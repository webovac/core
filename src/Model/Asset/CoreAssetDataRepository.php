<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Asset;

use App\Model\Asset\AssetData;
use Nette\Caching\Cache;


trait CoreAssetDataRepository
{
	private array $aliases;


	protected function getAliases(): array
	{
		if (!isset($this->aliases)) {
			$this->aliases = $this->cache->load(lcfirst($this->getName()) . 'Aliases', function () {
				$aliases = [];
				/** @var AssetData $asset */
				foreach ($this->getCollection() as $asset) {
					$aliases[$asset->name] = $asset->id;
				}
				return $aliases;
			}, [Cache::Tags => lcfirst($this->getName())]);
		}
		return $this->aliases;
	}
}
