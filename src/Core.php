<?php

declare(strict_types=1);

namespace Webovac\Core;


use App\Model\LanguageTranslation\LanguageTranslation;
use App\Model\ModuleTranslation\ModuleTranslation;
use App\Model\Orm;
use App\Model\PageTranslation\PageTranslation;
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
			$this->orm->languageTranslationRepository->$property[] = fn() => $this->cache->remove('language');
			$this->orm->moduleRepository->$property[] = fn() => $this->cache->remove('page');
			$this->orm->moduleTranslationRepository->$property[] = fn() => $this->cache->remove('page');
			$this->orm->pageRepository->$property[] = fn() => $this->cache->remove('page');
			$this->orm->pageTranslationRepository->$property[] = fn() => $this->cache->remove('page');
			$this->orm->webRepository->$property[] = fn() => $this->cache->remove('web');
			$this->orm->webTranslationRepository->$property[] = fn() => $this->cache->remove('web');
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
	}
}
