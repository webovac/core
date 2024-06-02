<?php

namespace Webovac\Core;


class Core implements Module
{
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
			new InstallGroup('language', 'Languages', iteration: 1),
			new InstallGroup('language', 'Languages', iteration: 2),
			new InstallGroup('module', 'Modules', ['language']),
			new InstallGroup('web', 'Webs', ['language', 'layout']),
		];
	}
}
