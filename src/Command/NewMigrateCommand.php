<?php

declare(strict_types=1);

namespace Webovac\Core\Command;

use App\Model\Orm;
use Nette\Utils\Finder;
use Nextras\Migrations\Drivers\PgSqlDriver;
use Nextras\Migrations\Extensions\SqlHandler;
use Nextras\Migrations\IDriver;
use Stepapo\Utils\Printer;
use Stepapo\Utils\Command\Command;
use Webovac\Core\Ext\Migrations\CmsConsoleController;
use Webovac\Core\Lib\NeonHandler;
use Webovac\Core\MigrationGroup;
use Webovac\Core\Module;


class NewMigrateCommand implements Command
{
	private Printer $printer;


	/** @param Module[] $modules */
	public function __construct(
		private array $modules,
		private \Stepapo\Manipulation\Lib\Processor $manipulationProcessor,
		private \Stepapo\Definition\Lib\DbProcessor $definitionProcessor,
		private IDriver $driver,
		private SqlHandler $sqlHandler,
		private Orm $orm,
	) {
		$this->printer = new Printer;
	}


	public function run(): int
	{
		# DEFINITIONS;
		$folders = [];
		foreach ($this->modules as $module) {
			$reflection = new \ReflectionClass($module);
			if (file_exists($dir = dirname($reflection->getFileName()) . "/definitions")) {
				$folders[] = $dir;
			}
		}
		$this->definitionProcessor->process($folders);

		# MANIPULATIONS
		$this->printer->printBigSeparator();
		$folders = [];
		$groups = [];
		foreach ($this->modules as $module) {
			$reflection = new \ReflectionClass($module);
			if (file_exists($dir = dirname($reflection->getFileName()) . "/manipulations")) {
				$folders[] = $dir;
			}
			if (!method_exists($module, 'getManipulationGroups')) {
				continue;
			}
			$groups = array_merge($groups, $module->getManipulationGroups());
		}
		$this->manipulationProcessor->process($folders, $groups);

		# SQL
		$this->printer->printBigSeparator();
		$controller = new CmsConsoleController($this->driver);
		foreach ($this->modules as $module) {
			if (!method_exists($module, 'getDefinitionGroup')) {
				continue;
			}
			$controller = $this->prepareDefinitions($module->getDefinitionGroup(), $controller);
		}
		$controller->addExtension('sql', $this->sqlHandler);
		$controller->run();
		$this->orm->flush();
		return 0;
	}


	/**
	 * @throws \ReflectionException
	 */
	public function prepareDefinitions(MigrationGroup $migrationGroup, CmsConsoleController $controller): CmsConsoleController
	{
		$reflection = new \ReflectionClass($migrationGroup->class);
		$files = [];
		$db = $this->driver instanceof PgSqlDriver ? 'pgsql' : 'mysql';
		if (file_exists($dir = dirname($reflection->getFileName()) . "/migrations/$db")) {
			$sqlFiles = Finder::findFiles("*.sql")->from($dir);
			$files = array_merge($files, $sqlFiles->collect());
		}
		$controller->addCmsGroup($migrationGroup->name, $migrationGroup, $files, $migrationGroup->dependencies);
		$_SERVER['argv'][] = $migrationGroup->name;
		return $controller;
	}
}