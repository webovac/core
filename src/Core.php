<?php

declare(strict_types=1);

namespace Webovac\Core;

use App\Model\Language\Language;
use App\Model\Language\LanguageData;
use App\Model\Log\Log;
use App\Model\Module\Module as ModuleEntity;
use App\Model\Module\ModuleData;
use App\Model\Orm;
use App\Model\Page\Page;
use App\Model\Person\Person;
use App\Model\Person\PersonData;
use App\Model\Role\RoleData;
use App\Model\Text\TextData;
use App\Model\Web\Web;
use App\Model\Web\WebData;
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


	public static function getCliSetup(): array
	{
		return ['icon' => '🌐', 'color' => 'white/black'];
	}


	public function getDefinitionGroup(): MigrationGroup
	{
		return new DefinitionGroup(Core::getModuleName(), Core::class);
	}


	public function getManipulationGroups(): array
	{
		return [
			new ManipulationGroup('web', WebData::class, ['layout']),
			new ManipulationGroup('role', RoleData::class),
			new ManipulationGroup('person', PersonData::class, ['role']),
			new ManipulationGroup('language', LanguageData::class),
			new ManipulationGroup('text', TextData::class, ['language']),
			new ManipulationGroup('module', ModuleData::class, ['language']),
			new ManipulationGroup('page', ModuleData::class, ['web', 'module', 'language']),
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
		if ($this->orm->hasRepositoryByName('logRepository')) {
			$this->orm->languageRepository->onAfterInsert[] = fn (Language $language) => $this->orm->logRepository->createLog($language, Log::TYPE_CREATE);
			$this->orm->moduleRepository->onAfterInsert[] = fn (ModuleEntity $module) => $this->orm->logRepository->createLog($module, Log::TYPE_CREATE);
			$this->orm->pageRepository->onAfterInsert[] = fn (Page $page) => $this->orm->logRepository->createLog($page, Log::TYPE_CREATE);
			$this->orm->webRepository->onAfterInsert[] = fn (Web $web) => $this->orm->logRepository->createLog($web, Log::TYPE_CREATE);
			$this->orm->personRepository->onAfterInsert[] = fn (Person $person) => $this->orm->logRepository->createLog($person, Log::TYPE_CREATE);
			$this->orm->languageRepository->onAfterUpdate[] = fn (Language $language) => $this->orm->logRepository->createLog($language, Log::TYPE_UPDATE);
			$this->orm->moduleRepository->onAfterUpdate[] = fn (ModuleEntity $module) => $this->orm->logRepository->createLog($module, Log::TYPE_UPDATE);
			$this->orm->pageRepository->onAfterUpdate[] = fn (Page $page) => $this->orm->logRepository->createLog($page, Log::TYPE_UPDATE);
			$this->orm->webRepository->onAfterUpdate[] = fn (Web $web) => $this->orm->logRepository->createLog($web, Log::TYPE_UPDATE);
			$this->orm->personRepository->onAfterUpdate[] = fn (Person $person) => $this->orm->logRepository->createLog($person, Log::TYPE_UPDATE);
		}
	}
}
