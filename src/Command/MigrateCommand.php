<?php

declare(strict_types=1);

namespace Webovac\Core\Command;

use App\Model\Orm;
use Nette\Utils\Finder;
use Nextras\Migrations\Drivers\PgSqlDriver;
use Nextras\Migrations\Extensions\SqlHandler;
use Nextras\Migrations\IDriver;
use Stepapo\Model\Definition\DbProcessor;
use Stepapo\Model\Manipulation\Processor;
use Stepapo\Utils\Printer;
use Stepapo\Utils\Command\Command;
use Tracy\Dumper;
use Webovac\Core\Ext\Migrations\CmsConsoleController;
use Webovac\Core\Lib\Dir;
use Webovac\Core\MigrationGroup;
use Webovac\Core\Module;


class MigrateCommand implements Command
{
	private Printer $printer;


	/** @param Module[] $modules */
	public function __construct(
		private array $modules,
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
		# DEFINITIONS;
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

		# MANIPULATIONS
		$folders = [];
		$groups = [];
		foreach ($this->modules as $module) {
			$reflection = new \ReflectionClass($module);
			if (file_exists($dir = dirname($reflection->getFileName()) . "/config/manipulations")) {
				$folders[] = $dir;
			}
			if (!method_exists($module, 'getManipulationGroups')) {
				continue;
			}
			$groups = array_merge($groups, $module->getManipulationGroups());
		}
		if (file_exists($dir = $this->dir->getAppDir() . '/../config/manipulations')) {
			$folders[] = $dir;
		}
		$this->manipulationProcessor->process($folders, $groups);

		# SQL
		$controller = new CmsConsoleController($this->driver);
		foreach ($this->modules as $module) {
			if (!method_exists($module, 'getDefinitionGroup')) {
				continue;
			}
			$controller = $this->prepareModule($module, $controller);
		}
		$controller->addExtension('sql', $this->sqlHandler);
		$controller->run();
		$this->orm->flush();
		return 0;
	}


	/**
	 * @throws \ReflectionException
	 */
	public function prepareModule(Module $module, CmsConsoleController $controller): CmsConsoleController
	{
		$reflection = new \ReflectionClass($module);
		$files = [];
		$db = $this->driver instanceof PgSqlDriver ? 'pgsql' : 'mysql';
		if (file_exists($dir = dirname($reflection->getFileName()) . "/config/migrations/$db")) {
			$sqlFiles = Finder::findFiles("*.sql")->from($dir);
			$files = array_merge($files, $sqlFiles->collect());
		}
		if (method_exists($module, 'getDefinitionGroup')) {
			$migrationGroup = $module->getDefinitionGroup();
			$controller->addCmsGroup($migrationGroup->name, $migrationGroup, $files, $migrationGroup->dependencies);
			$_SERVER['argv'][] = $migrationGroup->name;
		}
		return $controller;
	}
}