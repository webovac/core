<?php

declare(strict_types=1);

namespace Webovac\Core\Model;

use Build\Model\Deploy\DeployData;
use Build\Model\Language\LanguageData;
use Build\Model\Module\ModuleData;
use Build\Model\Page\PageData;
use Build\Model\TextTranslation\TextTranslationData;
use Build\Model\Web\WebData;
use Stepapo\Model\Data\Collection;


trait CoreDataModel
{
	/** @return PageData[] */
	public function findPageDatas(?WebData $webData = null): Collection
	{
		/** @var PageData[] $pageDatas */
		$pageDatas = $this->pageDataRepository->getCollection();
		if ($webData) {
			$result = [];
			foreach ($pageDatas as $key => $pageData) {
				if ($pageData->web === $webData->id) {
					$result[$key] = $pageData;
				}
			}
			return new Collection($result);
		}
		return $pageDatas;
	}


	public function getPageData(int $webId, int $pageId): ?PageData
	{
		return $this->pageDataRepository->getByKey("$webId-$pageId");
	}


	public function getPageDataByName(int $webId, string $pageName): ?PageData
	{
		$pageId = $this->pageDataRepository->getKey($webId, $pageName);
		return $pageId ? $this->getPageData($webId, $pageId) : null;
	}


	/** @return WebData[] */
	public function findWebDatas(): Collection
	{
		$webDatas = [];
		foreach ($this->webDataRepository->getAliases() as $webId) {
			$webDatas[] = $this->getWebData($webId);
		}
		return new Collection($webDatas);
	}


	public function getWebData(int $key): ?WebData
	{
		return $this->webDataRepository->getByKey($key);
	}


	public function getWebDataByHost(string $host, ?string $basePath): ?WebData
	{
		$webId = $this->webDataRepository->getKey($host, $basePath);
		return $webId ? $this->webDataRepository->getByKey($webId) : null;
	}


	public function getLanguageData(int $key): ?LanguageData
	{
		return $this->languageDataRepository->getByKey($key);
	}


	public function getLanguageDataByShortcut(string $shortcut): ?LanguageData
	{
		$languageId = $this->languageDataRepository->getKey($shortcut);
		return $languageId ? $this->getLanguageData($languageId) : null;
	}


	public function getModuleData(int $key): ?ModuleData
	{
		return $this->moduleDataRepository->getByKey($key);
	}


	public function getModuleDataByName(string $name): ?ModuleData
	{
		$moduleId = $this->moduleDataRepository->getKey($name);
		return $moduleId ? $this->getModuleData($moduleId) : null;
	}


	public function getTextTranslation(mixed $name, LanguageData $languageData): ?TextTranslationData
	{
		if (!$name) {
			return null;
		}
		return $this->textDataRepository
			->getByKey($name)
			?->getCollection('translations')
			->getByKey($languageData->id);
	}


	public function getLastDeployData(): ?DeployData
	{
		return $this->deployDataRepository->getLastDeployData();
	}
}