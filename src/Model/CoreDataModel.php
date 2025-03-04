<?php

declare(strict_types=1);

namespace Webovac\Core\Model;

use App\Model\Language\LanguageData;
use App\Model\Language\LanguageDataRepository;
use App\Model\Module\ModuleData;
use App\Model\Module\ModuleDataRepository;
use App\Model\Page\Page;
use App\Model\Page\PageData;
use App\Model\Page\PageDataRepository;
use App\Model\Person\PersonDataRepository;
use App\Model\Role\RoleDataRepository;
use App\Model\Text\TextDataRepository;
use App\Model\TextTranslation\TextTranslationData;
use App\Model\Web\WebData;
use App\Model\Web\WebDataRepository;
use Nette\DI\Attributes\Inject;
use ReflectionException;
use Stepapo\Model\Data\Collection;
use Webovac\Core\Lib\CmsUser;


trait CoreDataModel
{
	#[Inject] public LanguageDataRepository $languageRepository;
	#[Inject] public ModuleDataRepository $moduleRepository;
	#[Inject] public PageDataRepository $pageRepository;
	#[Inject] public TextDataRepository $textRepository;
	#[Inject] public WebDataRepository $webRepository;
	#[Inject] public PersonDataRepository $personRepository;
	#[Inject] public RoleDataRepository $roleRepository;


	/** @return Collection<PageData> */
	public function findPageDatas(): Collection
	{
		return $this->pageRepository->getCollection();
	}


	public function getPageData(int $webId, int $pageId): ?PageData
	{
		return $this->pageRepository->getByKey("$webId-$pageId");
	}


	public function getPageDataByName(int $webId, string $pageName): ?PageData
	{
		$pageId = $this->pageRepository->getKey($webId, $pageName);
		return $pageId ? $this->getPageData($webId, $pageId) : null;
	}


	/** @return Collection<WebData> */
	public function findWebDatas(): Collection
	{
		$webDatas = [];
		foreach ($this->webRepository->getAliases() as $webId) {
			$webDatas[] = $this->getWebData($webId);
		}
		return new Collection($webDatas);
	}


	public function getWebData(int $key): ?WebData
	{
		return $this->webRepository->getByKey($key);
	}


	public function getWebDataByHost(string $host, ?string $basePath): ?WebData
	{
		$webId = $this->webRepository->getKey($host, $basePath);
		return $webId ? $this->webRepository->getByKey($webId) : null;
	}


	public function getLanguageData(int $key): ?LanguageData
	{
		return $this->languageRepository->getByKey($key);
	}


	public function getLanguageDataByShortcut(string $shortcut): ?LanguageData
	{
		$languageId = $this->languageRepository->getKey($shortcut);
		return $languageId ? $this->getLanguageData($languageId) : null;
	}


	public function getModuleData(int $key): ?ModuleData
	{
		return $this->moduleRepository->getByKey($key);
	}


	public function getModuleDataByName(string $name): ?ModuleData
	{
		$moduleId = $this->moduleRepository->getKey($name);
		return $moduleId ? $this->getModuleData($moduleId) : null;
	}


	public function getTextTranslation(mixed $name, LanguageData $languageData): ?TextTranslationData
	{
		if (!$name) {
			return null;
		}
		return $this->textRepository
			->getByKey($name)
			?->getCollection('translations')
			->getByKey($languageData->id);
	}
}