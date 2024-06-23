<?php

declare(strict_types=1);

namespace Webovac\Core;

use App\Model\Language\Language;
use App\Model\LanguageTranslation\LanguageTranslation;
use App\Model\ModuleTranslation\ModuleTranslation;
use App\Model\Orm;
use App\Model\Page\Page;
use App\Model\PageTranslation\PageTranslation;
use App\Model\Web\Web;
use App\Model\WebTranslation\WebTranslation;
use Nette\Caching\Cache;


class Core implements Module
{
	public function __construct(
		private Orm $orm,
		private Cache $cache,
	) {
		$this->registerOrmEvents();
	}


	public static function getModuleName(): string
	{
		return 'core';
	}


	public function getMigrationGroup(): MigrationGroup
	{
		return new MigrationGroup(Core::getModuleName(), __DIR__ . '/migrations');
	}


	public function getInstallGroups(): array
	{
		return [
			new InstallGroup('role', 'Roles'),
			new InstallGroup('person', 'Persons', ['role']),
			new InstallGroup('language', 'Languages'),
			new InstallGroup('module', 'Modules', ['language']),
			new InstallGroup('web', 'Webs', ['language', 'layout']),
		];
	}


	public function registerOrmEvents(): void
	{
		foreach (['onAfterPersist', 'onAfterRemove'] as $property) {
			$this->orm->languageRepository->$property[] = fn() => $this->cache->remove('language');
			$this->orm->moduleRepository->$property[] = fn() => $this->cache->remove('page');
			$this->orm->pageRepository->$property[] = fn() => $this->cache->remove('page');
			$this->orm->webRepository->$property[] = fn() => $this->cache->remove('web');
		}
		if ($this->orm->hasRepositoryByName('indexTranslationRepository')) {
			$this->orm->languageTranslationRepository->onAfterPersist[] = function (LanguageTranslation $languageTranslation) {
				$this->orm->indexTranslationRepository->createIndexTranslation(
					$languageTranslation->language,
					'language',
					$languageTranslation->translationLanguage,
					['A' => $languageTranslation->language->name, 'B' => $languageTranslation->title],
				);
			};
			$this->orm->moduleTranslationRepository->onAfterPersist[] = function (ModuleTranslation $moduleTranslation) {
				$this->orm->indexTranslationRepository->createIndexTranslation(
					$moduleTranslation->module,
					'module',
					$moduleTranslation->language,
					['A' => $moduleTranslation->module->name, 'B' => $moduleTranslation->title, 'C' => $moduleTranslation->description],
				);
			};
			$this->orm->pageTranslationRepository->onAfterPersist[] = function (PageTranslation $pageTranslation) {
				$this->orm->indexTranslationRepository->createIndexTranslation(
					$pageTranslation->page,
					'page',
					$pageTranslation->language,
					['A' => $pageTranslation->page->name, 'B' => $pageTranslation->title, 'C' => $pageTranslation->description],
				);
			};
			$this->orm->webTranslationRepository->onAfterPersist[] = function (WebTranslation $webTranslation) {
				$this->orm->indexTranslationRepository->createIndexTranslation(
					$webTranslation->web,
					'web',
					$webTranslation->language,
					['A' => $webTranslation->web->code, 'B' => $webTranslation->title],
				);
			};
		}
		if ($this->orm->hasRepositoryByName('logRepository')) {
			$this->orm->languageRepository->onAfterInsert[] = fn (Language $language) => $this->orm->logRepository->createLog($language, 'createdLanguage', $language->createdByPerson, $language->createdAt);
			$this->orm->moduleRepository->onAfterInsert[] = fn (\App\Model\Module\Module $module) => $this->orm->logRepository->createLog($module, 'createdModule', $module->createdByPerson, $module->createdAt);
			$this->orm->pageRepository->onAfterInsert[] = fn (Page $page) => $this->orm->logRepository->createLog($page, 'createdPage', $page->createdByPerson, $page->createdAt);
			$this->orm->webRepository->onAfterInsert[] = fn (Web $web) => $this->orm->logRepository->createLog($web, 'createdWeb', $web->createdByPerson, $web->createdAt);
			$this->orm->languageRepository->onAfterUpdate[] = fn (Language $language) => $this->orm->logRepository->createLog($language, 'updatedLanguage', $language->updatedByPerson, $language->updatedAt);
			$this->orm->moduleRepository->onAfterUpdate[] = fn (\App\Model\Module\Module $module) => $this->orm->logRepository->createLog($module, 'updatedModule', $module->updatedByPerson, $module->updatedAt);
			$this->orm->pageRepository->onAfterUpdate[] = fn (Page $page) => $this->orm->logRepository->createLog($page, 'updatedPage', $page->updatedByPerson, $page->updatedAt);
			$this->orm->webRepository->onAfterUpdate[] = fn (Web $web) => $this->orm->logRepository->createLog($web, 'updatedWeb', $web->updatedByPerson, $web->updatedAt);
		}
	}
}
