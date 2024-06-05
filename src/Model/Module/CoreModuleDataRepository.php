<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Module;

use App\Model\Module\ModuleData;
use App\Model\ModuleTranslation\ModuleTranslationDataRepository;
use App\Model\Page\PageDataRepository;
use Nette\DI\Attributes\Inject;


trait CoreModuleDataRepository
{
	#[Inject] public ModuleTranslationDataRepository $moduleTranslationDataRepository;
	#[Inject] public PageDataRepository $pageDataRepository;


	public function createDataFromConfig(array $config, string $mode, ?int $iteration = null): ModuleData
	{
		/** @var ModuleData $data */
		$data = $this->processor->process($this->getSchema($mode), $config);
		$rank = 1;
		foreach ($data->tree as $parentPage => $pages) {
			$this->processTree((array) $pages, $parentPage, $rank++, $data);
		}
		foreach ($data->translations as $key => $translationConfig) {
			$translationConfig['language'] ??= $key;
			unset($data->translations[$key]);
			$data->translations[$translationConfig['language']] = $this->moduleTranslationDataRepository->createDataFromConfig($translationConfig, $mode);
		}
		foreach ($data->pages as $key => $pageConfig) {
			if (!$this->checkPage($key, $data)) {
				unset($data->pages[$key]);
				continue;
			}
			$pageConfig['name'] ??= $key;
			unset($data->pages[$key]);
			$data->pages[$pageConfig['name']] = $this->pageDataRepository->createDataFromConfig($pageConfig, $mode);
		}
		return $data;
	}


	private function checkPage(string $page, ModuleData $data)
	{
		if (isset($data->pages[$page])) {
			$p = $data->pages[$page];
			$relatedPage = $p['targetPage'] ?? ($p['redirectPage'] ?? null);
			if (!$relatedPage) {
				return true;
			}
			return $this->checkPage(str_contains($relatedPage, ':') ? strtok($relatedPage, ':') : $relatedPage, $data);
		}
		return false;
	}


	private function processTree(array $pages, string $parentPage, int $rank, ModuleData &$data): void
	{
		$r = 1;
		$data->pages[$parentPage]['rank'] = $rank;
		foreach ($pages as $page => $subPages) {
			if (!$this->checkPage($page, $data)) {
				continue;
			}
			$data->pages[$page]['parentPage'] = $parentPage;
			$this->processTree((array) $subPages, $page, $r++, $data);
		}
	}
}