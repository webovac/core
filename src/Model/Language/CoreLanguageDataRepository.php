<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Language;

use App\Model\Language\LanguageData;


trait CoreLanguageDataRepository
{
	private array $aliases;


	protected function getAliases(): array
	{
		if (!isset($this->aliases)) {
			$this->aliases = $this->cache->load(lcfirst($this->getName()) . '_aliases', function () {
				$aliases = [];
				/** @var LanguageData $page */
				foreach ($this->getCollection() as $language) {
					$aliases[$language->shortcut] = $language->id;
				}
				return $aliases;
			});
		}
		return $this->aliases;
	}


	public function findAllPairs(): array
	{
		$return = [];
		foreach ($this->findAll() as $languageData) {
			$return[$languageData->id] = $languageData->shortcut;
		}
		return $return;
	}


	public function getId(string $shortcut): ?int
	{
		return $this->getAliases()[$shortcut] ?? null;
	}
}