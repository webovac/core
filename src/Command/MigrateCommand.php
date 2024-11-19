<?php

declare(strict_types=1);

namespace Webovac\Core\Command;

use App\Model\Orm;
use Nette\Utils\Finder;
use Nextras\Migrations\Drivers\PgSqlDriver;
use Nextras\Migrations\Extensions\SqlHandler;
use Nextras\Migrations\IDriver;
use Stepapo\Model\Definition\DbProcessor;
use Stepapo\Model\Definition\HasDefinitionGroup;
use Stepapo\Model\Manipulation\HasManipulationGroups;
use Stepapo\Model\Manipulation\Processor;
use Stepapo\Utils\Command\Command;
use Stepapo\Utils\Printer;
use Webovac\Core\Ext\Migrations\CmsConsoleController;
use Webovac\Core\Lib\Dir;
use Webovac\Core\Module;


class MigrateCommand implements Command
{
	private Printer $printer;


	/**
	 * @param Module[] $modules
	 * @param HasDefinitionGroup[] $withDefinitionGroup
	 * @param HasManipulationGroups[] $withManipulationGroups
	 */
	public function __construct(
		private array $modules,
		private array $withDefinitionGroup,
		private array $withManipulationGroups,
		private Processor $manipulationProcessor,
		private DbProcessor $definitionProcessor,
		private IDriver $driver,
		private SqlHandler $sqlHandler,
		private Orm $orm,
		private Dir $dir,
	) {
		$this->printer = new Printer;
	}


	public function run(): int
	{
		$this->runDefinitions();
		$this->runManipulations();
		$this->runMigrations();
		return 0;
	}


	public function runDefinitions(): int
	{
		$folders = [];
		foreach ($this->modules as $module) {
			$reflection = new \ReflectionClass($module);
			if (file_exists($dir = dirname($reflection->getFileName()) . "/config/definitions")) {
				$folders[] = $dir;
			}
		}
		if (file_exists($dir = $this->dir->getAppDir() . '/../config/definitions')) {
			$folders[] = $dir;
		}
		$this->definitionProcessor->process($folders);
		return 0;
	}


	public function runManipulations(): int
	{
		$folders = [];
		$groups = [];
		foreach ($this->modules as $module) {
			$reflection = new \ReflectionClass($module);
			if (file_exists($dir = dirname($reflection->getFileName()) . "/config/manipulations")) {
				$folders[] = $dir;
			}
		}
		foreach ($this->withManipulationGroups as $hasManipulationGroups) {
			$groups = array_merge($groups, $hasManipulationGroups->getManipulationGroups());
		}
		if (file_exists($dir = $this->dir->getAppDir() . '/../config/manipulations')) {
			$folders[] = $dir;
		}
		$this->manipulationProcessor->process($folders, $groups);
		return 0;
	}


	public function runMigrations(): int
	{
		$controller = new CmsConsoleController($this->driver);
		foreach ($this->withDefinitionGroup as $hasDefinitionGroup) {
			$controller = $this->prepareModule($hasDefinitionGroup, $controller);
		}
		$controller->addExtension('sql', $this->sqlHandler);
		$controller->run();
		$this->orm->flush();
		return 0;
	}


	public function prepareModule(HasDefinitionGroup $hasDefinitionGroup, CmsConsoleController $controller): CmsConsoleController
	{
		$reflection = new \ReflectionClass($hasDefinitionGroup);
		$files = [];
		$db = $this->driver instanceof PgSqlDriver ? 'pgsql' : 'mysql';
		if (file_exists($dir = dirname($reflection->getFileName()) . "/config/migrations/$db")) {
			$sqlFiles = Finder::findFiles("*.sql")->from($dir);
			$files = array_merge($files, $sqlFiles->collect());
		}
		$migrationGroup = $hasDefinitionGroup->getDefinitionGroup();
		$controller->addCmsGroup($migrationGroup->name, $migrationGroup, $files, $migrationGroup->dependencies);
		$_SERVER['argv'][] = $migrationGroup->name;
		return $controller;
	}
}