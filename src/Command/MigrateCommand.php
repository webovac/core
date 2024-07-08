<?php

declare(strict_types=1);

namespace Webovac\Core\Command;

use App\Model\Orm;
use Nette\Utils\FileInfo;
use Nette\Utils\Finder;
use Nextras\Migrations\Drivers\PgSqlDriver;
use Nextras\Migrations\Extensions\SqlHandler;
use Nextras\Migrations\IDriver;
use Webovac\Core\Ext\Migrations\CmsConsoleController;
use Webovac\Core\Lib\NeonHandler;
use Webovac\Core\MigrationGroup;
use Webovac\Core\Module;


class MigrateCommand implements Command
{
	private array $paths = [];
	/** @var MigrationGroup[] */ private array $groups = [];


	/** @param Module[] $modules */ 
	public function __construct(
		private Orm $orm,
		private IDriver $driver,
		private SqlHandler $sqlHandler,
		private NeonHandler $neonHandler,
		private array $modules
	) {
		foreach ($modules as $module) {
			$reflection = new \ReflectionClass($module);
			if (file_exists($path = dirname($reflection->getFileName()) . '/migrations/manipulations')) {
				$this->paths[] = $path;
			}
			if (!method_exists($module, 'getManipulationGroups')) {
				continue;
			}
			$this->groups = array_merge($this->groups, $module->getManipulationGroups());
		}
	}


	public function run(): int
	{
		$controller = new CmsConsoleController($this->driver);
		foreach($this->modules as $module) {
			if (!method_exists($module, 'getDefinitionGroup')) {
				continue;
			}
			$controller = $this->prepareDefinitions($module->getDefinitionGroup(), $controller);
		}
		foreach($this->groups as $group) {
			$controller = $this->prepareManipulations($group, $controller);
		}
		$controller->addExtension('sql', $this->sqlHandler);
		$controller->addExtension('neon', $this->neonHandler);
		$controller->run();
		$this->orm->flush();
		return 0;
	}


	public function prepareDefinitions(MigrationGroup $migrationGroup, CmsConsoleController $controller): CmsConsoleController
	{
		$reflection = new \ReflectionClass($migrationGroup->class);
		$files = [];
		if (file_exists($dir = dirname($reflection->getFileName()) . "/migrations/definitions")) {
			$neonFiles = Finder::findFiles("*.neon")->from($dir);
			$files = array_merge($files, $neonFiles->collect());
		}
		$db = $this->driver instanceof PgSqlDriver ? 'pgsql' : 'mysql';
		if (file_exists($dir = dirname($reflection->getFileName()) . "/migrations/definitions/$db")) {
			$sqlFiles = Finder::findFiles("*.sql")->from($dir);
			$files = array_merge($files, $sqlFiles->collect());
		}
		$controller->addCmsGroup($migrationGroup->name, $migrationGroup, $files, $migrationGroup->dependencies, 'create');
		$_SERVER['argv'][] = $migrationGroup->name;
		return $controller;
	}


	public function prepareManipulations(MigrationGroup $migrationGroup, CmsConsoleController $controller): CmsConsoleController
	{
		$files = Finder::findFiles("*.$migrationGroup->name.*.neon")->from($this->paths ?? [])->sortBy(
			fn(FileInfo $a, FileInfo $b) => $a->getFilename() <=> $b->getFilename()
		)->collect() ?: [];
		$controller->addCmsGroup($migrationGroup->name, $migrationGroup, $files, $migrationGroup->dependencies);
		$_SERVER['argv'][] = $migrationGroup->name;
		return $controller;
	}
}