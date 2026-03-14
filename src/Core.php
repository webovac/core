<?php

declare(strict_types=1);

namespace Webovac\Core;

use Build\Model\Asset\AssetData;
use Build\Model\Language\Language;
use Build\Model\Language\LanguageData;
use Build\Model\Lib\LibData;
use Build\Model\Module\Module as ModuleEntity;
use Build\Model\Module\ModuleData;
use Build\Model\Orm;
use Build\Model\Page\Page;
use Build\Model\Page\PageData;
use Build\Model\Person\PersonData;
use Build\Model\Role\RoleData;
use Build\Model\Text\TextData;
use Build\Model\Web\Web;
use Build\Model\Web\WebData;
use Nette\Caching\Cache;
use Stepapo\Model\Definition\DefinitionGroup;
use Stepapo\Model\Definition\HasDefinitionGroup;
use Stepapo\Model\Manipulation\HasManipulationGroups;
use Stepapo\Model\Manipulation\ManipulationGroup;
use Webovac\Core\Lib\CmsCache;


class Core implements Module, HasDefinitionGroup, HasManipulationGroups, HasOrmEvents
{
	public function __construct(
		private Orm $orm,
		private CmsCache $cmsCache,
	) {}


	public static function getModuleName(): string
	{
		return 'core';
	}


	public static function getCliSetup(): array
	{
		return ['icon' => '🌐', 'color' => 'white/black'];
	}


	public function getDefinitionGroup(): DefinitionGroup
	{
		return new DefinitionGroup(Core::getModuleName(), Core::class);
	}


	public function getManipulationGroups(): array
	{
		return [
			AssetData::class => new ManipulationGroup('asset', AssetData::class, ['lib']),
			LibData::class => new ManipulationGroup('lib', LibData::class),
			WebData::class => new ManipulationGroup('web', WebData::class, ['layout']),
			RoleData::class => new ManipulationGroup('role', RoleData::class),
			PersonData::class => new ManipulationGroup('person', PersonData::class, ['role']),
			LanguageData::class => new ManipulationGroup('language', LanguageData::class),
			TextData::class => new ManipulationGroup('text', TextData::class, ['language']),
			ModuleData::class => new ManipulationGroup('module', ModuleData::class, ['language']),
			PageData::class => new ManipulationGroup('page', PageData::class, ['web', 'module', 'language', 'lib']),
		];
	}


	public function registerOrmEvents(): void
	{
		foreach (['onAfterPersist', 'onAfterRemove'] as $property) {
			$this->orm->languageRepository->$property[] = fn(Language $language) => $this->cmsCache->clean([Cache::Tags => ['language', 'web', 'page', 'layout']]);
			$this->orm->moduleRepository->$property[] = fn(ModuleEntity $module) => $this->cmsCache->clean([Cache::Tags => ['page', 'web']]);
			$this->orm->pageRepository->$property[] = fn(Page $page) => $this->cmsCache->clean([Cache::Tags => ['page', 'web']]);
			$this->orm->webRepository->$property[] = fn(Web $web) => $this->cmsCache->clean([Cache::Tags => ['page', 'web']]);
		}
	}
}
