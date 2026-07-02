<?php

declare(strict_types=1);

namespace Webovac\Core\Lib;

use Build\Model\DataModel;
use Build\Model\Orm;
use Build\Model\Page\Page;
use Build\Model\Page\PageData;
use Nette\InvalidStateException;
use Stepapo\Model\Data\Collection;
use Stepapo\Utils\Clearable;
use Stepapo\Utils\Service;
use Webovac\Core\Exception\LoginRequiredException;
use Webovac\Core\Exception\MissingPermissionException;
use Webovac\Core\HasPageSetups;
use Webovac\Core\Model\CmsEntity;
use function array_key_exists, is_int;


class PageRequirementChecker implements Service, Clearable
{
	private array $checked = [];
	private array $setups = [];


	/** @param HasPageSetups[] $hasPageSetups */
	public function __construct(
		private array $hasPageSetups,
		private Orm $orm,
		private CmsUser $cmsUser,
		private DataModel $dataModel,
		private DataProvider $dataProvider,
	) {
		foreach ($this->hasPageSetups as $hasPageSetups) {
			foreach ($hasPageSetups->getPageSetups() as $name => $setup) {
				if (isset($this->setups[$name])) {
					throw new InvalidStateException("Duplicate page setup for '$name'.");
				}
				$this->setups[$name] = $setup;
			}
		}
	}


	/**
	 * @throws LoginRequiredException
	 * @throws MissingPermissionException
	 */
	public function checkPageRequirements(PageData $pageData): void
	{
		if (array_key_exists($pageData->name, $this->checked) && $this->checked[$pageData->name]) {
			return;
		}
		$webData = $this->dataProvider->getWebData();
		if (
			isset($this->setups[$pageData->name])
			&& !($this->setups[$pageData->name])($this->orm, $this->cmsUser, $webData)
		) {
			throw new MissingPermissionException;
		}
		foreach ($pageData->accessSetups as $accessSetup) {
			$accessSetup->checkRequirements($this->cmsUser, $webData);
		}
	}


	public function isPageAccessible(PageData $pageData, ?CmsEntity $entity = null): bool
	{
		if (array_key_exists($pageData->name, $this->checked)) {
			return $this->checked[$pageData->name];
		}
		if (
			isset($this->setups[$pageData->name])
			&& !($this->setups[$pageData->name])($this->orm, $this->cmsUser, $this->dataProvider->getWebData())
		) {
			$this->checked[$pageData->name] = false;
			return false;
		}
		$return = $this->isUserAuthorizedToPage($pageData) && $this->isPageTagAllowed($pageData, $entity);
		$this->checked[$pageData->name] = $return;
		return $return;
	}


	/**
	 * @param Collection<PageData> $pageDatas
	 * @return Collection<PageData>
	 */
	public function filterPages(Collection $pageDatas, ?CmsEntity $entity): Collection
	{
		$filteredPageDatas = [];
		foreach ($pageDatas as $pageData) {
			$add = true;
			if (!$this->isPageAccessible($pageData, $entity)) {
				$add = false;
			}
			if ($add && $pageData->type === Page::TYPE_INTERNAL_LINK) {
				assert(is_int($pageData->targetPage));
				$targetPageData = $this->dataModel->getPageData($this->dataProvider->getWebData()->id, $pageData->targetPage);
				if (!$this->isPageAccessible($targetPageData, $entity)) {
					$add = false;
				}
			}
			if ($add) {
				$filteredPageDatas[] = $pageData;
			}
		}
		return new Collection($filteredPageDatas);
	}


	private function isUserAuthorizedToPage(PageData $pageData): bool
	{
		foreach ($pageData->accessSetups as $accessSetup) {
			if (!$accessSetup->isUserAuthorized($this->cmsUser, $this->dataProvider->getWebData())) {
				return false;
			}
		}
		return true;
	}


	private function isPageTagAllowed(PageData $pageData, ?CmsEntity $entity): bool
	{
		if ($pageData->authorizingTag && $entity) {
			if (!$entity->check($this->cmsUser, $this->dataProvider->getWebData(), $pageData->authorizingTag)) {
				return false;
			}
		}
		return true;
	}


	public function clear(): void
	{
		$this->checked = [];
	}
}
