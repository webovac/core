<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Language;

use Build\Model\Language\LanguageData;
use Nette\Caching\Cache;


trait CoreLanguageDataRepository
{
	private array $aliases;


	protected function getAliases(): array
	{
		if (!isset($this->aliases)) {
			$this->aliases = $this->cache->load('aliases', function () {
				$aliases = [];
				/** @var LanguageData $page */
				foreach ($this->getCollection() as $language) {
					$aliases[$language->shortcut] = $language->id;
				}
				return $aliases;
			}, [Cache::Tags => lcfirst($this->getName())]);
		}
		return $this->aliases;
	}


	public function findAllPairs(): array
	{
		return array_flip($this->getAliases());
	}


	public function getKey(string $shortcut): ?int
	{
		return $this->getAliases()[$shortcut] ?? null;
	}
}