<?php

namespace Webovac\Core\Command;

use Nextras\Migrations\Controllers\ConsoleController;
use Nextras\Migrations\Drivers\PgSqlDriver;
use Nextras\Migrations\Extensions\SqlHandler;
use Nextras\Migrations\IDriver;
use Webovac\Core\Ext\Migrations\CmsConsoleController;
use Webovac\Core\MigrationGroup;
use Webovac\Core\Module;


class MigrateCommand implements Command
{
	/** @param Module[] $modules */
	public function __construct(
		private IDriver $driver,
		private array $modules
	) {}


	public function run(): int
	{
		$controller = new CmsConsoleController($this->driver);
		foreach($this->modules as $module) {
			if (!method_exists($module, 'getMigrationGroup')) {
				continue;
			}
			$controller = $this->prepare($module->getMigrationGroup(), $controller);
		}
		$controller->addExtension('sql', new SqlHandler($this->driver));
		$controller->run();
		return 0;
	}


	public function prepare(MigrationGroup $migrationGroup, ConsoleController $controller): ConsoleController
	{
		$db = $this->driver instanceof PgSqlDriver ? 'pgsql' : 'mysql';
		$controller->addGroup($migrationGroup->name, "$migrationGroup->dir/$db", $migrationGroup->dependencies);
		$_SERVER['argv'][] = $migrationGroup->name;

		return $controller;
	}
}