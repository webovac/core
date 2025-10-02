<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Lib;

use Build\Model\Lib\LibData;
use Nette\Caching\Cache;


trait CoreLibDataRepository
{
	private array $aliases;


	protected function getAliases(): array
	{
		if (!isset($this->aliases)) {
			$this->aliases = $this->cache->load('aliases', function () {
				$aliases = [];
				/** @var LibData $lib */
				foreach ($this->getCollection() as $lib) {
					$aliases[$lib->name] = $lib->id;
				}
				return $aliases;
			}, [Cache::Tags => lcfirst($this->getName())]);
		}
		return $this->aliases;
	}
}
