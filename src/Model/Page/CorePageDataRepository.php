<?php

namespace Webovac\Core\Model\Page;

use App\Model\Page\Page;
use App\Model\Page\PageData;
use App\Model\PageTranslation\PageTranslationDataRepository;
use Nette\DI\Attributes\Inject;
use Webovac\Core\Lib\Collection;


trait CorePageDataRepository
{
	#[Inject] public PageTranslationDataRepository $pageTranslationDataRepository;


	protected function getCollection(): Collection
	{
		if (!isset($this->collection)) {
			$this->collection = $this->cache->load(lcfirst($this->getName()), function () {
				$collection = new Collection;
				$this->buildCollection($collection);
				$array = (array) $collection;
				uasort($array, function(PageData $a, PageData $b) {
					return [$a->host, $a->basePath, $a->hasParentParameter, $a->hasParameter]
						<=> [$b->host, $b->basePath, $b->hasParentParameter, $b->hasParameter];
				});
				return new Collection($array);
			});
		}
		return $this->collection;
	}


	/**
	 * @param Collection<PageData> $collection
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
			$pageData->host = $parentPageData ? $parentPageData->host : $page->web->host;
			$pageData->basePath = $parentPageData ? $parentPageData->basePath : $page->web->basePath;
			$pageData->accessSetups = array_merge($parentPageData->accessSetups ?? [], [$accessSetup]);
			$pageData->isHomePage = $page->isHomePage();
			$pageData->navigationPage = $page->providesNavigation ? $page->id : ($parentPageData->navigationPage ?? null);
			$pageData->buttonsPage = $page->providesButtons ? $page->id : ($parentPageData->buttonsPage ?? null);
			$pageData->parentPages = array_merge($parentPageData->parentPages ?? [], $page->type === Page::TYPE_MODULE ? [] : [$page->id]);
			$pageData->parentPage = $page->parentPage?->id ?: ($parentPageData->parentPage ?? null);
			foreach ($page->translations as $translation) {
				$parentPath = $parentPageData?->getCollection('translations')->getBy(['language' => $translation->language->id])
					? $parentPageData?->getCollection('translations')->getBy(['language' => $translation->language->id])->fullPath
					: '//'
					. $pageData->host
					. ($pageData->basePath ? ('/' . $pageData->basePath) : '');
				$pageData->translations[$translation->id]->fullPath = $parentPath . ($translation->path ? '/' . $translation->path : '');
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


	public function createDataFromConfig(array $config, string $mode, ?int $iteration = null): PageData
	{
		/** @var PageData $data */
		$data = $this->processor->process($this->getSchema($mode), $config);
		if (isset($data->translations)) {
			foreach ($data->translations as $key => $translationConfig) {
				$translationConfig['language'] ??= $key;
				unset($data->translations[$key]);
				$data->translations[$translationConfig['language']] = $this->pageTranslationDataRepository->createDataFromConfig($translationConfig, $mode);
			}
		}
		if (isset($data->pages)) {
			foreach ($data->pages as $key => $pageConfig) {
				$pageConfig['name'] ??= $key;
				unset($data->pages[$key]);
				$data->pages[$pageConfig['name']] = $this->createDataFromConfig($pageConfig, $mode);
			}
		}
		return $data;
	}
}