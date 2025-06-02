<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Page;

use App\Model\Asset\Asset;
use App\Model\Module\Module;
use App\Model\Orm;
use App\Model\Page\Page;
use App\Model\Page\PageData;
use App\Model\Page\PageDataRepository;
use App\Model\PageTranslation\PageTranslationDataRepository;
use App\Model\Web\WebDataRepository;
use Nette\Caching\Cache;
use Nette\DI\Attributes\Inject;
use ReflectionException;
use Stepapo\Model\Data\Collection;
use Stepapo\Model\Data\Item;
use Throwable;


trait CorePageDataRepository
{
	private array $aliases;
	#[Inject] public PageTranslationDataRepository $pageTranslationDataRepository;


	/** @return Collection<Item> */
	public function getCollection(): Collection
	{
		if (!isset($this->collection)) {
			$this->buildCache();
		}
		return $this->collection;
	}


	/**
	 * @throws Throwable
	 */
	protected function buildCache(): void
	{
		$this->cache->remove('routeSetup');
		$this->cache->remove('pageAliases');
		$this->cache->clean([Cache::Tags => lcfirst($this->getName())]);
		$this->orm->pageTranslationRepository->rebuildPaths();
		$this->orm->flush();
		$collection = new Collection;
		$this->buildCollection($collection);
		$array = (array) $collection;
		uasort($array, function(PageData $a, PageData $b) {
			return [$a->host, $a->basePath, $a->hasPath, $a->hasParameter]
				<=> [$b->host, $b->basePath, $b->hasPath, $b->hasParameter];
		});
		foreach ($collection as $key => $item) {
			$this->cacheItem($key, $item);
		}
		$this->collection = $collection;
	}


	/**
	 * @throws Throwable
	 */
	protected function getAliases(): array
	{
		if (!isset($this->aliases)) {
			$this->aliases = $this->cache->load(lcfirst($this->getName()) . 'Aliases', function () {
				$aliases = [];
				/** @var PageData $page */
				foreach ($this->getCollection() as $page) {
					$aliases["$page->web-$page->name"] = $page->id;
				}
				return $aliases;
			}, [Cache::Tags => lcfirst($this->getName())]);
		}
		return $this->aliases;
	}


	/**
	 * @param Collection<PageData> $collection
	 * @param HasPages|null $hasPages
	 * @param PageData|null $parentPageData
	 * @param int $rank
	 * @throws ReflectionException
	 */
	private function buildCollection(
		Collection &$collection,
		?HasPages $hasPages = null,
		?PageData $parentPageData = null,
		int &$rank = 1,
	): void
	{
		$pages = $hasPages
			? $hasPages->getPages()
			: $this->orm->pageRepository->findRootPages();
		/** @var Page $page */
		foreach ($pages as $page) {
			$accessSetup = new AccessSetup;
			$accessSetup->accessFor = $page->accessFor;
			$accessSetup->authorizedRoles = $page->accessFor === Page::ACCESS_FOR_SPECIFIC
				? $page->authorizedRoles->toCollection()->fetchPairs(null, 'code')
				: [];
			$accessSetup->authorizedPersons = $page->accessFor === Page::ACCESS_FOR_SPECIFIC
				? $page->authorizedPersons->toCollection()->fetchPairs(null, 'id')
				: [];
			unset($pageData);
			$pageData = $page->getData(forCache: true);
			if ($hasPages instanceof Module && $page === $hasPages->homePage) {
				$pageData->icon = $parentPageData->icon;
				$pageData->hideInNavigation = $parentPageData->hideInNavigation;
			}
			$pageData->web = $parentPageData ? $parentPageData->web : $page->web->id;
			$pageData->module = $page->module?->id;
			$pageData->host = $parentPageData ? $parentPageData->host : $page->web->host;
			$pageData->basePath = $parentPageData ? $parentPageData->basePath : $page->web->basePath;
			$pageData->accessSetups = $pageData->dontInheritAccessSetup ? [$accessSetup] : array_merge($parentPageData->accessSetups ?? [], [$accessSetup]);
			$pageData->authorizingTag = $page->authorizingTag ?: $parentPageData?->authorizingTag;
			$pageData->isHomePage = $page->isHomePage();
			$pageData->navigationPage = $page->providesNavigation ? $page->id : ($parentPageData->navigationPage ?? null);
			$pageData->buttonsPage = $page->providesButtons ? $page->id : ($parentPageData->buttonsPage ?? null);
			$pageData->hasParameter = ($page->hasParameter ?: $parentPageData?->hasParameter) ?: false;
			$pageData->repository = $page->repository ?: $parentPageData?->repository;
			$pageData->parentPages = array_merge($parentPageData->parentPages ?? [], $page->type === Page::TYPE_MODULE ? [] : [$page->id]);
			$p = $page->translations->toCollection()->fetch()?->path;
			$pageData->isDetailRoot = $p && str_contains($p, '<');
			$pageData->hasPath = ($p ? str_contains($p, '<path') : false) ?: ($parentPageData?->hasPath ?: false);
			$pageData->parentDetailRootPages = array_merge($parentPageData->parentDetailRootPages ?? [], $pageData->isDetailRoot ? [$page->id] : []);
			$pageData->parentPage = $page->parentPage?->id ?: ($parentPageData->parentPage ?? null);
			$pageData->childPageIds = [];
			foreach ($page->translations as $translation) {
				$parentPageTranslationData = $parentPageData?->getCollection('translations')->getByKey($translation->language->id);
				if ($hasPages instanceof Module && $page === $hasPages->homePage) {
					$pageData->translations[$translation->language->id]->title = $parentPageTranslationData?->title ?: $translation->title;
				}
			}
			if ($pageData->parentPage && $pageData->type !== Page::TYPE_MODULE) {
				$parentPageData = $collection[$pageData->web . '-' . $pageData->parentPage];
				if ($pageData->type !== Page::TYPE_PAGE || $pageData->hasParameter === $parentPageData->hasParameter) {
					$collection[$pageData->web . '-' . $pageData->parentPage]->childPageIds[] = $page->id;
				}
			}
			foreach ($page->libs as $lib) {
				foreach ($lib->assets as $asset) {
					if ($asset->type === Asset::TYPE_STYLESHEET) {
						$pageData->stylesheets[] = $asset->getData();
					} else if ($asset->type === Asset::TYPE_SCRIPT) {
						$pageData->scripts[] = $asset->getData();
					}
				}
			}
			if ($page->type === Page::TYPE_MODULE) {
				$this->buildCollection($collection, $page->targetModule, $pageData, $rank);
			} else {
				$pageData->rank = $rank++;
				$collection[$pageData->web . '-' . $page->id] = $pageData;
				$this->buildCollection($collection, $page, $pageData);
			}
		}
	}


	public function getKey(int $webId, string $pageName): ?int
	{
		return $this->getAliases()["$webId-$pageName"] ?? null;
	}
}