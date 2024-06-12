<?php

declare(strict_types=1);

namespace Webovac\Core\Model;

use App\Model\Language\LanguageData;
use App\Model\Language\LanguageDataRepository;
use App\Model\Module\ModuleDataRepository;
use App\Model\Page\Page;
use App\Model\Page\PageData;
use App\Model\Page\PageDataRepository;
use App\Model\Person\PersonDataRepository;
use App\Model\Role\RoleDataRepository;
use App\Model\Web\WebData;
use App\Model\Web\WebDataRepository;
use Nette\DI\Attributes\Inject;
use Webovac\Core\Lib\CmsUser;
use Webovac\Core\Lib\Collection;


trait CoreDataModel
{
	#[Inject] public LanguageDataRepository $languageRepository;
	#[Inject] public ModuleDataRepository $moduleRepository;
	#[Inject] public PageDataRepository $pageRepository;
	#[Inject] public WebDataRepository $webRepository;
	#[Inject] public PersonDataRepository $personRepository;
	#[Inject] public RoleDataRepository $roleRepository;
	#[Inject] public CmsUser $cmsUser;


	public function getPageData(int $webId, int $pageId): ?PageData
	{
		return $this->pageRepository->getById($webId . '-' . $pageId);
	}


	public function getPageDataByName(int $webId, string $pageName): ?PageData
	{
		return $this->pageRepository->getBy(['web' => $webId, 'name' => $pageName]);
	}


	public function getHomePageData(int $webId): ?PageData
	{
		return $this->pageRepository->getBy(['web' => $webId, 'isHomePage' => true]);
	}


	public function getWebDatas(): Collection
	{
		return $this->webRepository->findAll();
	}


	public function getWebDataByHost(string $host, ?string $basePath): ?WebData
	{
		return $this->webRepository->getBy(['host' => $host, 'basePath' => $basePath]);
	}


	public function getLanguageData(int $id): ?LanguageData
	{
		return $this->languageRepository->getById($id);
	}


	public function getLanguageDataByShortcut(string $shortcut): ?LanguageData
	{
		return $this->languageRepository->getBy(['shortcut' => $shortcut]);
	}


	/** @return Collection<PageData> */ 
	public function getRootPageDatas(WebData $webData, LanguageData $languageData): Collection
	{
		$array = (array) $this->pageRepository->findAll();
		$pageDatas = array_filter($array, function($pageData) use ($webData, $languageData) {
			if ($pageData->web !== $webData->id) {
				return false;
			}
			if (count($pageData->parentPages) > 1) {
				return false;
			}
//			if ($pageData->type === Page::TYPE_PAGE && !$pageData->getCollection('translations')->getBy(['language' => $languageData->id])) {
//				return false;
//			}
			if (!$pageData->isUserAuthorized($this->cmsUser)) {
				return false;
			}
			if (
				$pageData->type === Page::TYPE_INTERNAL_LINK
				&& !$this->getPageData($webData->id, $pageData->targetPage)->isUserAuthorized($this->cmsUser)
			) {
				return false;
			}
			return true;
		});
		uasort($pageDatas, fn(PageData $a, PageData $b) => $a->rank <=> $b->rank);
		return new Collection($pageDatas);
	}


	/** @return Collection<PageData> */ 
	public function getChildPageDatas(WebData $webData, PageData $parentPageData, LanguageData $languageData): Collection
	{
		$array = (array) $this->pageRepository->findAll();
		$pageDatas = array_filter($array, function($pageData) use ($webData, $parentPageData, $languageData) {
			if ($pageData->web !== $webData->id) {
				return false;
			}
			if ($pageData->parentPage !== $parentPageData->id) {
				return false;
			}
			if ($pageData->type === Page::TYPE_PAGE && (
//					!$pageData->getCollection('translations')->getBy(['language' => $languageData->id]) ||
					$pageData->hasParameter !== $parentPageData->hasParameter
					|| $pageData->hasParentParameter !== $parentPageData->hasParentParameter
				)) {
				return false;
			}
			if (!$pageData->isUserAuthorized($this->cmsUser)) {
				return false;
			}
			if (
				$pageData->type === Page::TYPE_INTERNAL_LINK
				&& !$this->getPageData($webData->id, $parentPageData->targetPage)->isUserAuthorized($this->cmsUser)
			) {
				return false;
			}
			return true;
		});
		uasort($pageDatas, fn($a, $b) => $a->rank <=> $b->rank);
		return new Collection($pageDatas);
	}
}