<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Page;

use App\Model\Index\Index;
use App\Model\Module\Module;
use App\Model\Page\Page;
use App\Model\Page\PageData;
use App\Model\PageTranslation\PageTranslation;
use App\Model\PageTranslation\PageTranslationRepository;
use App\Model\Person\Person;
use App\Model\Web\Web;
use App\Model\Web\WebData;
use Nextras\Dbal\Utils\DateTimeImmutable;
use Nextras\Orm\Collection\ICollection;
use Nextras\Orm\Entity\IEntity;
use Tracy\Dumper;
use Webovac\Core\Lib\CmsUser;
use Webovac\Core\Model\CmsDataRepository;
use Webovac\Core\Model\Web\WebModuleData;


trait CorePageRepository
{
	public function getByParameters(mixed $parameter, mixed $parentParameter, ?string $parentRepository = null): ?Page
	{
		if ($parentRepository === 'module') {
			return $this->getBy(['module->id' => $parentParameter, 'id' => $parameter]);
		}
		return $this->getBy(['web->id' => $parentParameter, 'id' => $parameter]);
	}


	/** @return ICollection<Page> */ 
	public function findRootPages(): ICollection
	{
		return $this->findBy(['parentPage' => null, 'web!=' => null])->orderBy('rank');
	}


	public function createModulePage(Web $web, Module $module, ?Person $person = null, ?int $count = null): void
	{
		$count ??= $web->getPages()->countStored();
		$page = new Page;
		$page->web = $web;
		$page->module = $module;
		$page->name = $module->name . 'Module';
		$page->type = Page::TYPE_MODULE;
		$page->rank = $count + 1;
		$page->createdByPerson = $person;
		$this->persist($page);
		foreach ($module->translations as $translation) {
			$pageTranslation = new PageTranslation;
			$pageTranslation->path = $translation->basePath;
			$pageTranslation->title = $translation->title;
			$pageTranslation->createdByPerson = $person;
			$pageTranslation->page = $page;
			$pageTranslation->language = $translation->language;
			$this->getModel()->getRepository(PageTranslationRepository::class)->persist($pageTranslation);
		}
	}


	public function removeModulePage(Web $web, Module $module): void
	{
		$modulePage = $this->getBy(['web' => $web, 'module' => $module]);
		$this->removePage($modulePage);
	}


	public function buildFromModule(Web $web, Module $module, ?Person $person = null, ?int $count = null): void
	{
		if (!$web->modules->has($module)) {
			$web->modules->add($module);
			$this->getModel()->persist($web);
		}
		$count ??= $web->getPages()->count();
		foreach ($module->pages->toCollection()->findBy(['web' => null]) as $templatePage) {
			$this->createFromTemplatePage($templatePage, $web, $person, $count);
		}
		foreach ($this->findBy(['web' => $web, 'templatePage->module' => $module]) as $page) {
			if ($page->templatePage->parentPage) {
				$page->parentPage = $this->getBy(['web' => $web, 'templatePage' => $page->templatePage->parentPage]);
			}
			$this->persist($page);
		}
	}


	public function removeModule(Web $web, Module $module): void
	{
		$web->modules->remove($module);
		$this->getModel()->persist($web);
		foreach ($this->findBy(['web' => $web, 'module' => $module]) as $page) {
			$this->removePage($page);
		}
	}


	public function createFromTemplatePage(Page $templatePage, Web $web, ?Person $person, int $count): Page
	{
		$page = $this->getBy(['web' => $web, 'templatePage' => $templatePage]);
		if ($page) {
			return $page;
		}

		$now = new DateTimeImmutable;

		$page = new Page;
		$page->module = $templatePage->module;
		$page->icon = $templatePage->icon;
		$page->name = $templatePage->name;
		$page->type = $templatePage->type;
		$page->stretched = $templatePage->stretched;
		$page->hasParameter = $templatePage->hasParameter;
		$page->hasParentParameter = $templatePage->hasParentParameter;
		$page->providesNavigation = $templatePage->providesNavigation;
		$page->providesButtons = $templatePage->providesButtons;
		$page->hideInNavigation = $templatePage->hideInNavigation;
		$page->repository = $templatePage->repository;
		$page->parentRepository = $templatePage->parentRepository;
		$page->targetParameter = $templatePage->targetParameter;
		$page->targetParentParameter = $templatePage->targetParentParameter;
		$page->targetSignal = $templatePage->targetSignal;
		$page->targetUrl = $templatePage->targetUrl;
		$page->rank = $templatePage->parentPage ? $templatePage->rank : ($templatePage->rank + $count);
		$page->createdAt = new DateTimeImmutable;
		$page->updatedAt = $templatePage->updatedAt;
		$page->web = $web;
		$page->templatePage = $templatePage;
		$page->createdByPerson = $person;
		$page->accessFor = $templatePage->accessFor;
		if ($templatePage->targetPage) {
			if ($targetPage = $this->getBy(['web' => $web, 'templatePage' => $templatePage->targetPage])) {
				$page->targetPage = $targetPage;
			} else {
				$page->targetPage = $this->createFromTemplatePage($templatePage->targetPage, $web, $person, $count);
			}
		}

		foreach ($templatePage->translations as $templatePageTranslation) {
			$pageTranslation = new PageTranslation;
			$pageTranslation->path = $templatePageTranslation->path;
			$pageTranslation->title = $templatePageTranslation->title;
			$pageTranslation->content = $templatePageTranslation->content;
			$pageTranslation->page = $page;
			$pageTranslation->language = $templatePageTranslation->language;
			$pageTranslation->createdAt = $now;
			$pageTranslation->createdByPerson = $person;
			$this->getModel()->persist($pageTranslation);
		}

		foreach ($templatePage->authorizedPersons as $authorizedPerson) {
			$page->authorizedPersons->add($authorizedPerson);
		}

		foreach ($templatePage->authorizedRoles as $authorizedRole) {
			$page->authorizedRoles->add($authorizedRole);
		}

		$this->persist($page);

		return $page;
	}


	public function postProcessFromData(PageData $data, Page $page, ?Person $person = null, string $mode = CmsDataRepository::MODE_INSTALL): Page
	{
		if (isset($data->redirectPage)) {
			$page->redirectPage = $this->getBy(
				is_int($data->redirectPage)
					? ['id' => $data->redirectPage]
					: [
						ICollection::AND,
						['name' => $data->redirectPage],
						[ICollection::OR, 'web' => $page->web, 'module' => $page->web ? $page->web->modules->toCollection()->fetchPairs(null, 'id') : $page->module],
					]
			);
		}
		if (isset($data->type) && $data->type === Page::TYPE_INTERNAL_LINK) {
			if (isset($data->targetPage)) {
				$page->targetPage = $this->getBy(
					is_int($data->targetPage)
						? ['id' => $data->targetPage]
						: [
						ICollection::AND,
						['name' => $data->targetPage],
						[ICollection::OR, 'web' => $page->web, 'module' => $page->web ? $page->web->modules->toCollection()->fetchPairs(null, 'id') : $page->module],
					]
				);
			}
		}
		if (isset($data->parentPage)) {
			$page->parentPage = $this->getBy(
				is_int($data->parentPage)
					? ['id' => $data->parentPage]
					: [
					ICollection::AND,
					['name' => $data->parentPage],
					[ICollection::OR, 'web' => $page->web, 'module' => $page->web ? $page->web->modules->toCollection()->fetchPairs(null, 'id') : $page->module],
				]
			);
		}
		$this->persist($page);
		return $page;
	}


	public function postProcessModulePageFromData(WebModuleData $data, Page $page): Page
	{
		if (isset($data->parentPage)) {
			$page->parentPage = $this->getBy(
				is_int($data->parentPage)
					? ['id' => $data->parentPage]
					: [
					ICollection::AND,
					['name' => $data->parentPage],
					[ICollection::OR, 'web' => $page->web, 'module' => $page->web ? $page->web->modules->toCollection()->fetchPairs(null, 'id') : $page->module],
				]
			);
		}
		$this->persist($page);
		return $page;
	}


	public function getByData(PageData|string $data, ?HasPages $hasPages): ?Page
	{
		if (!$hasPages) {
			return null;
		}
		$code = $data instanceof PageData ? $data->name : $data;
		assert($hasPages instanceof IEntity);
		if (!$hasPages->isPersisted()) {
			return is_int($code) ? $this->getById($code) : null;
		}
		return $this->getBy([
			'name' => $data instanceof PageData ? $data->name : $data,
			$hasPages instanceof Web ? 'web' : 'module' => $hasPages,
		]);
	}


	public function movePage(Page $movedPage, ?Page $oldParentPage, int $oldRank, ?Page $newParentPage, int $newRank): void
	{
		$this->getMapper()->movePage($movedPage, $oldParentPage, $oldRank, $newParentPage, $newRank);
	}


	public function removePage(Page $page): void
	{
		$this->getMapper()->removePage($page);
	}


	public function getIndexFilter(WebData $webData, CmsUser $cmsUser): array
	{
		return [
			ICollection::AND,
			['page->hasParameter' => false],
			['page->type' => [Page::TYPE_PAGE, Page::TYPE_SIGNAL]],
			['page!=' => null],
			[
				ICollection::OR,
				['page->web' => $webData->id],
				[
					ICollection::AND,
					'page->web' => null,
					'page->module->webs->id' => $webData->id,
				]
			]
		];
	}
}
