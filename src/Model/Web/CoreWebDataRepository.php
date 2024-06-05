<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Web;

use App\Model\Page\PageDataRepository;
use App\Model\Web\WebData;
use App\Model\WebTranslation\WebTranslationDataRepository;
use Nette\DI\Attributes\Inject;
use Nextras\Orm\Collection\ICollection;
use Webovac\Core\Lib\CmsExpect;
use Webovac\Core\Lib\Collection;
use Webovac\Core\Model\CmsData;


trait CoreWebDataRepository
{
	#[Inject] public WebTranslationDataRepository $webTranslationDataRepository;
	#[Inject] public PageDataRepository $pageDataRepository;


	/** @return Collection<CmsData> */
	protected function getCollection(): Collection
	{
		if (!isset($this->collection)) {
			$this->collection = $this->cache->load(lcfirst($this->getName()), function () {
				$collection = new Collection;
				foreach ($this->getOrmRepository()->findAll()->orderBy('basePath', ICollection::ASC_NULLS_FIRST) as $entity) {
					$collection[$entity->getPersistedId()] = $entity->getData();
				}
				return $collection;
			});
		}
		return $this->collection;
	}


	public function createDataFromConfig(array $config, string $mode, ?int $iteration = null): WebData
	{
		if (!$this->moduleChecker->isModuleInstalled('style')) {
			unset($config['layout']);
		}
		/** @var WebData $data */
		$data = $this->processor->process($this->getSchema($mode), $config);
		$rank = 1;
		$this->processWebModules($data);
		if (isset($data->tree)) {
			foreach ($data->tree as $parentPage => $pages) {
				if (!$this->checkPage($parentPage, $data)) {
					continue;
				}
				$this->processTree((array) $pages, $parentPage, $rank++, $data);
			}
		}
		if (isset($data->translations)) {
			foreach ($data->translations as $key => $translationConfig) {
				$translationConfig['language'] ??= $key;
				unset($data->translations[$key]);
				$data->translations[$translationConfig['language']] = $this->webTranslationDataRepository->createDataFromConfig($translationConfig, $mode);
			}
		}
		if (isset($data->pages)) {
			foreach ($data->pages as $key => $pageConfig) {
				if (!$this->checkPage($key, $data)) {
					unset($data->pages[$key]);
					continue;
				}
				$pageConfig['name'] ??= $key;
				unset($data->pages[$key]);
				$data->pages[$pageConfig['name']] = $this->pageDataRepository->createDataFromConfig($pageConfig, $mode);
			}
		}
		if (isset($data->webModules)) {
			foreach ($data->webModules as $key => $webModuleConfig) {
				$webModuleConfig['name'] ??= $key;
				unset($data->webModules[$key]);
				$data->webModules[$webModuleConfig['name']] = $this->processor->process(CmsExpect::fromDataClass(WebModuleData::class, $mode), $webModuleConfig);
			}
		}
		return $data;
	}


	private function processWebModules(WebData &$config): void
	{
		if (!isset($config->webModules)) {
			return;
		}
		foreach ($config->webModules as $key => $moduleName) {
			unset($config->webModules[$key]);
			if (!$this->moduleChecker->isModuleInstalled(lcfirst($moduleName))) {
				continue;
			}
			$config->webModules[$moduleName] = [];
		}
	}


	private function checkPage(string $page, WebData $data)
	{
		if (isset($data->pages[$page])) {
			$p = $data->pages[$page];
			$relatedPage = $p['targetPage'] ?? ($p['redirectPage'] ?? null);
			if (!$relatedPage) {
				return true;
			}
			return $this->checkPage(str_contains($relatedPage, ':') ? strtok($relatedPage, ':') : $relatedPage, $data);
		}
		if (isset($data->webModules[$page])) {
			return $this->moduleChecker->isModuleInstalled(lcfirst($page));
		}
		return false;
	}


	private function processTree(array $pages, string $parentPage, int $rank, WebData &$data): void
	{
		$r = 1;
		if (isset($data->pages[$parentPage])) {
			$data->pages[$parentPage]['rank'] = $rank;
		} else {
			$data->webModules[$parentPage]['rank'] = $rank;
		}
		foreach ($pages as $page => $subPages) {
			if (!$this->checkPage($page, $data)) {
				continue;
			}
			if (isset($data->pages[$page])) {
				$data->pages[$page]['parentPage'] = $parentPage;
			} else {
				$data->webModules[$page]['parentPage'] = $parentPage;
			}
			$this->processTree((array) $subPages, $page, $r++, $data);
		}
	}
}