<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Lib;

use App\Model\Lib\LibData;
use Nette\Caching\Cache;


trait CoreLibDataRepository
{
	private array $aliases;


	protected function getAliases(): array
	{
		if (!isset($this->aliases)) {
			$this->aliases = $this->cache->load(lcfirst($this->getName()) . 'Aliases', function () {
				$aliases = [];
				/** @var LibData $Lib */
				foreach ($this->getCollection() as $Lib) {
					$aliases[$Lib->name] = $Lib->id;
				}
				return $aliases;
			}, [Cache::Tags => lcfirst($this->getName())]);
		}
		return $this->aliases;
	}
}
