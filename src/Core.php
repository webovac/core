<?php

declare(strict_types=1);

namespace Webovac\Core;

use Build\Model\Asset\AssetData;
use Build\Model\Command\CommandData;
use Build\Model\Language\LanguageData;
use Build\Model\Lib\LibData;
use Build\Model\Module\ModuleData;
use Build\Model\Page\PageData;
use Build\Model\Person\PersonData;
use Build\Model\Role\RoleData;
use Build\Model\Text\TextData;
use Build\Model\Web\WebData;
use Stepapo\Model\Definition\DefinitionGroup;
use Stepapo\Model\Definition\HasDefinitionGroup;
use Stepapo\Model\Manipulation\HasManipulationGroups;
use Stepapo\Model\Manipulation\ManipulationGroup;


class Core implements Module, HasDefinitionGroup, HasManipulationGroups
{
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
			CommandData::class => new ManipulationGroup('command', CommandData::class),
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
}
