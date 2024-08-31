<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Page;

use App\Model\Page\Page;
use App\Model\Page\PageData;
use App\Model\PageTranslation\PageTranslationDataRepository;
use Nette\DI\Attributes\Inject;
use ReflectionException;
use Stepapo\Utils\Model\Collection;
use Throwable;


trait CorePageDataRepository
{
	#[Inject] public PageTranslationDataRepository $pageTranslationDataRepository;


	/**
	 * @throws Throwable
	 */
	protected function getCollection(): Collection
	{
		if (!isset($this->collection)) {
			$this->collection = $this->cache->load(lcfirst($this->getName()), function () {
				$this->cache->remove('routeList');
				$collection = new Collection;
				$this->buildCollection($collection);
				$array = (array) $collection;
				uasort($array, function(PageData $a, PageData $b) {
					return [$a->host, $a->basePath, $a->hasPath, $a->hasParameter]
						<=> [$b->host, $b->basePath, $b->hasPath, $b->hasParameter];
				});
				return new Collection($array);
			});
		}
		return $this->collection;
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
			$pageData = $page->getData();
			$pageData->web = $parentPageData ? $parentPageData->web : $page->web->id;
			$pageData->module = $page->module?->id;
			$pageData->host = $parentPageData ? $parentPageData->host : $page->web->host;
			$pageData->basePath = $parentPageData ? $parentPageData->basePath : $page->web->basePath;
			$pageData->accessSetups = $pageData->dontInheritAccessSetup ? [$accessSetup] : array_merge($parentPageData->accessSetups ?? [], [$accessSetup]);
			$pageData->isHomePage = $page->isHomePage();
			$pageData->navigationPage = $page->providesNavigation ? $page->id : ($parentPageData->navigationPage ?? null);
			$pageData->buttonsPage = $page->providesButtons ? $page->id : ($parentPageData->buttonsPage ?? null);
			$pageData->repository = $page->repository ?: $parentPageData?->repository;
			$pageData->parentPages = array_merge($parentPageData->parentPages ?? [], $page->type === Page::TYPE_MODULE ? [] : [$page->id]);
			$p = $page->translations->toCollection()->fetch()?->path;
			$pageData->isDetailRoot = $p && str_contains($p, '<');
			$pageData->hasPath = ($p ? str_contains($p, '<path') : false) ?: ($parentPageData?->hasPath ?: false);
			$pageData->parentDetailRootPages = array_merge($parentPageData->parentPages ?? [], $pageData->isDetailRoot ? [$page->id] : []);
			$pageData->parentPage = $page->parentPage?->id ?: ($parentPageData->parentPage ?? null);
			foreach ($page->translations as $translation) {
				$parentPath = !$pageData->dontInheritPath && $parentPageData?->getCollection('translations')->getBy(['language' => $translation->language->id])
					? $parentPageData?->getCollection('translations')->getBy(['language' => $translation->language->id])->fullPath
					: '//' . $pageData->host . ($pageData->basePath ? ('/' . $pageData->basePath) : '');
				$path = $translation->path ? preg_replace('/<id(.*)>/', "<id[" . $pageData->name . "]>", $translation->path) : null;
				$pageData->translations[$translation->id]->fullPath = $parentPath . ($path ? '/' . $path : '');
			}
			if ($page->type === Page::TYPE_MODULE) {
				$this->buildCollection($collection, $page->module, $pageData, $rank);
			} else {
				$pageData->rank = $rank++;
				$collection[$pageData->web . '-' . $page->id] = $pageData;
				$this->buildCollection($collection, $page, $pageData);
			}
		}
	}
}