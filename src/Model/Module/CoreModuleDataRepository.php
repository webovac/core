<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Module;

use Build\Model\Module\ModuleData;
use Nette\Caching\Cache;


trait CoreModuleDataRepository
{
	private array $aliases;


	protected function getAliases(): array
	{
		if (!isset($this->aliases)) {
			$this->aliases = $this->cache->load('aliases', function () {
				$aliases = [];
				foreach ($this->getCollection() as $module) {
					\assert($module instanceof ModuleData);
					$aliases[$module->name] = $module->id;
				}
				return $aliases;
			}, [Cache::Tags => lcfirst($this->getName())]);
		}
		return $this->aliases;
	}


	public function getKey(string $name): ?int
	{
		return $this->getAliases()[$name] ?? null;
	}
}
