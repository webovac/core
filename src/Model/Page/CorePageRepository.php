<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Page;

use App\Model\Module\Module;
use App\Model\Page\Page;
use App\Model\Page\PageData;
use App\Model\PageTranslation\PageTranslation;
use App\Model\PageTranslation\PageTranslationRepository;
use App\Model\Person\Person;
use App\Model\Web\Web;
use App\Model\Web\WebData;
use Nette\InvalidArgumentException;
use Nextras\Dbal\Utils\DateTimeImmutable;
use Nextras\Orm\Collection\ICollection;
use Stepapo\Model\Data\Item;
use Webovac\Core\Model\CmsEntity;


trait CorePageRepository
{
	public function getByParameters(?array $parameters = null, ?string $path = null, ?WebData $webData = null): ?Page
	{
		if (isset($parameters['ModuleDetail'])) {
			return $this->getBy(['module->name' => $parameters['ModuleDetail'], 'name' => $parameters['TemplateDetail']]);
		}
		return $this->getBy(['name' => $parameters['Admin:PageDetail'], 'web->id' => $webData->id]);
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
		$page->hideInNavigation = $module->homePage->hideInNavigation;
		$page->icon = $module->icon;
		$page->targetModule = $module;
//		$page->module = $module;
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
		$modulePage = $this->getBy(['web' => $web, 'targetModule' => $module]);
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
		$page->providesNavigation = $templatePage->providesNavigation;
		$page->providesButtons = $templatePage->providesButtons;
		$page->hideInNavigation = $templatePage->hideInNavigation;
		$page->repository = $templatePage->repository;
		$page->targetParameter = $templatePage->targetParameter;
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


	public function postProcessFromData(Item $data, CmsEntity $entity, bool $skipDefaults = false): Page
	{
		if (!$data instanceof PageData || !$entity instanceof Page) {
			throw new InvalidArgumentException;
		}
		if (isset($data->redirectPage)) {
			$entity->redirectPage = $this->getBy(
				is_int($data->redirectPage)
					? ['id' => $data->redirectPage]
					: [
						ICollection::AND,
						['name' => $data->redirectPage],
						$entity->web ? [ICollection::OR, 'web' => $entity->web, 'module' => $entity->web->modules->toCollection()->fetchPairs(null, 'id')] : ['module' => $entity->module],
					]
			);
		}
		if (isset($data->type) && $data->type === Page::TYPE_INTERNAL_LINK) {
			if (isset($data->targetPage)) {
				$entity->targetPage = $this->getBy(
					is_int($data->targetPage)
						? ['id' => $data->targetPage]
						: [
							ICollection::AND,
							['name' => $data->targetPage],
							$entity->web ? [ICollection::OR, 'web' => $entity->web, 'module' => $entity->web->modules->toCollection()->fetchPairs(null, 'id')] : ['module' => $entity->module],
						]
				);
			}
		}
		if (isset($data->parentPage)) {
			$entity->parentPage = $this->getBy(
				is_int($data->parentPage)
					? ['id' => $data->parentPage]
					: [
						ICollection::AND,
						['name' => $data->parentPage],
						$entity->web ? [ICollection::OR, 'web' => $entity->web, 'module' => $entity->web->modules->toCollection()->fetchPairs(null, 'id')] : ['module' => $entity->module],
					]
			);
		}
		$this->persist($entity);
		return $entity;
	}


	public function getByData(PageData|string $data, ?HasPages $hasPages = null): ?Page
	{
		$code = $data instanceof PageData ? $data->name : $data;
		$filter = ['name' => $code];
		if (!$hasPages) {
			if (isset($data->web) || isset($data->module)) {
				if (isset($data->web)) {
					$filter[is_numeric($data->web) ? 'web' : 'web->code'] = $data->web;
				}
				if (isset($data->module)) {
					$filter[is_numeric($data->module) ? 'module' : 'module->name'] = $data->module;
				}
				return $this->getBy($filter);
			}
			return null;
		}
		if ($hasPages instanceof Page) {
			$filter[$hasPages->web ? 'web' : 'module'] = $hasPages->web ?: $hasPages->module;
			return $this->getBy($filter);
		}
		assert($hasPages instanceof CmsEntity);
		if (!$hasPages->isPersisted()) {
			return is_numeric($code) ? $this->getById($code) : null;
		}
		$filter[$hasPages instanceof Web ? 'web' : 'module'] = $hasPages;
		return $this->getBy($filter);
	}


	public function movePage(Page $movedPage, ?Page $oldParentPage, int $oldRank, ?Page $newParentPage, int $newRank): void
	{
		$this->getMapper()->movePage($movedPage, $oldParentPage, $oldRank, $newParentPage, $newRank);
	}


	public function removePage(Page $page): void
	{
		$this->getMapper()->removePage($page);
	}
}
