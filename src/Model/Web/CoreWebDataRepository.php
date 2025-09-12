<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Web;

use App\Model\Page\PageDataRepository;
use App\Model\Web\Web;
use App\Model\WebTranslation\WebTranslationDataRepository;
use Nette\Caching\Cache;
use Nette\DI\Attributes\Inject;
use Nextras\Orm\Collection\ICollection;
use Stepapo\Model\Data\Collection;
use Stepapo\Model\Data\Item;
use Throwable;


trait CoreWebDataRepository
{
	#[Inject] public WebTranslationDataRepository $webTranslationDataRepository;
	#[Inject] public PageDataRepository $pageDataRepository;
	private array $aliases;


	/**
	 * @return Collection<Item>
	 * @throws Throwable
	 */
	public function buildCache(): void
	{
		if (isset($this->collection)) {
			return;
		}
		$this->cmsCache->remove('routeSetup');
		$this->cmsCache->remove('webAliases');
		$this->cmsCache->clean([Cache::Tags => lcfirst($this->getName())]);
		$pageDatas = $this->pageDataRepository->getCollection();
		$allPages = [];
		foreach ($pageDatas as $pageData) {
			if (!$pageData->web) {
				continue;
			}
			$allPages[$pageData->web][$pageData->web . '-' . $pageData->id] = $pageData;
		}
		foreach ($this->getOrmRepository()->findAll()->orderBy('basePath', ICollection::ASC_NULLS_FIRST) as $entity) {
			$key = $this->getIdentifier($entity);
			$item = $entity->getData(forCache: true);
			$item->adminRoles = $entity->adminRoles->toCollection()->fetchPairs(null, 'code');
			$item->adminPersons = $entity->adminPersons->toCollection()->fetchPairs(null, 'id');
			$rootPageIds = [];
			foreach ($entity->getPagesForMenu() as $page) {
				$rootPageIds[] = $page->id;
			}
			$item->rootPages = $rootPageIds;
			$item->allPages = $allPages[$entity->id] ?? [];
			$this->cacheItem($key, $item);
			$this->addItemToCollection($key, $item);
		}
	}


	public function getAliases(): array
	{
		if (!isset($this->aliases)) {
			$this->aliases = $this->cache->load(lcfirst($this->getName()) . 'Aliases', function () {
				$aliases = [];
				/** @var Web $web */
				foreach ($this->getOrmRepository()->findAll()->orderBy('basePath', ICollection::ASC_NULLS_FIRST) as $web) {
					$aliases["$web->host-$web->basePath"] = $web->id;
				}
				return $aliases;
			}, [Cache::Tags => lcfirst($this->getName())]);
		}
		return $this->aliases;
	}


	public function getKey(string $host, ?string $basePath): ?int
	{
		return $this->getAliases()["$host-$basePath"] ?? null;
	}
}