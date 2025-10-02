<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Module;

use Build\Model\Module\ModuleData;
use Build\Model\ModuleTranslation\ModuleTranslationDataRepository;
use Build\Model\Page\PageDataRepository;
use Nette\Caching\Cache;
use Nette\DI\Attributes\Inject;


trait CoreModuleDataRepository
{
	#[Inject] public ModuleTranslationDataRepository $moduleTranslationDataRepository;
	#[Inject] public PageDataRepository $pageDataRepository;
	private array $aliases;


	protected function getAliases(): array
	{
		if (!isset($this->aliases)) {
			$this->aliases = $this->cache->load('aliases', function () {
				$aliases = [];
				/** @var ModuleData $module */
				foreach ($this->getCollection() as $module) {
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