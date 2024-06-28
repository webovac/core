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
use Webovac\Core\InstallGroup;
use Webovac\Core\Lib\NeonHandler;
use Webovac\Core\MigrationGroup;
use Webovac\Core\Model\CmsDataRepository;
use Webovac\Core\Module;


class MigrateCommand implements Command
{
	private array $paths = [];
	/** @var InstallGroup[] */ private array $groups = [];


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
			if (file_exists($installPath = dirname($reflection->getFileName()) . '/migrations/data/install')) {
				$this->paths[CmsDataRepository::MODE_INSTALL][] = $installPath;
			}
			if (file_exists($updatePath = dirname($reflection->getFileName()) . '/migrations/data/update')) {
				$this->paths[CmsDataRepository::MODE_UPDATE][] = $updatePath;
			}
			if (!method_exists($module, 'getInstallGroups')) {
				continue;
			}
			$this->groups = array_merge($this->groups, $module->getInstallGroups());
		}
	}


	public function run(): int
	{
		$controller = new CmsConsoleController($this->driver);
		foreach($this->modules as $module) {
			if (!method_exists($module, 'getMigrationGroup')) {
				continue;
			}
			$controller = $this->prepareMigrations($module->getMigrationGroup(), $controller);
		}
		foreach($this->groups as $group) {
			$controller = $this->prepareInstalls($group, $controller);
		}
		foreach($this->groups as $group) {
			$controller = $this->prepareInstalls($group, $controller, CmsDataRepository::MODE_UPDATE);
		}
		$controller->addExtension('sql', $this->sqlHandler);
		$controller->addExtension('neon', $this->neonHandler);
		$controller->run();
		$this->orm->flush();
		return 0;
	}


	public function prepareMigrations(MigrationGroup $migrationGroup, CmsConsoleController $controller): CmsConsoleController
	{
		$reflection = new \ReflectionClass($migrationGroup->moduleClass);
		if (file_exists($dir = dirname($reflection->getFileName()) . "/migrations/structures/create")) {
			$files = Finder::findFiles("*.neon")->from($dir);
			if ($files->collect()) {
				$controller->addCmsGroup($migrationGroup->name . '-create', $files->collect(), $migrationGroup->dependencies, 'create');
			}
		} else {
			$controller->addCmsGroup($migrationGroup->name . '-create', [], $migrationGroup->dependencies, 'create');
		}
		$_SERVER['argv'][] = $migrationGroup->name . '-create';
		if (file_exists($dir = dirname($reflection->getFileName()) . "/migrations/structures/alter")) {
			$files = Finder::findFiles("*.neon")->from($dir);
			if ($files->collect()) {
				$migrationGroup->dependencies[] = $migrationGroup->name . '-create';
				$controller->addCmsGroup($migrationGroup->name . '-alter', $files->collect(), $migrationGroup->dependencies, 'alter');
			}
		} else {
			$controller->addCmsGroup($migrationGroup->name . '-alter', [], $migrationGroup->dependencies, 'alter');
		}
		$_SERVER['argv'][] = $migrationGroup->name . '-alter';
		$db = $this->driver instanceof PgSqlDriver ? 'pgsql' : 'mysql';
		if (file_exists($dir = dirname($reflection->getFileName()) . "/migrations/structures/$db")) {
			$files = Finder::findFiles("*.sql")->from($dir);
			if ($files->collect()) {
				$migrationGroup->dependencies[] = $migrationGroup->name . '-alter';
				$controller->addCmsGroup($migrationGroup->name, $files->collect(), $migrationGroup->dependencies);
			}
		} else {
			$controller->addCmsGroup($migrationGroup->name, [], $migrationGroup->dependencies);
		}
		$_SERVER['argv'][] = $migrationGroup->name;

		return $controller;
	}


	public function prepareInstalls(InstallGroup $group, CmsConsoleController $controller, string $mode = CmsDataRepository::MODE_INSTALL): CmsConsoleController
	{
		$files = Finder::findFiles("$group->name.*.neon")->from($this->paths[$mode] ?? [])->sortBy(
			fn(FileInfo $a, FileInfo $b) => $a->getFilename() <=> $b->getFilename()
		);
		if ($files->collect()) {
			if ($mode === CmsDataRepository::MODE_UPDATE) {
				$group->dependencies = [$group->name . '-' . CmsDataRepository::MODE_INSTALL];
			}
			$controller->addCmsGroup($group->name . '-' . $mode, $files->collect(), $group->dependencies, $mode);
			$_SERVER['argv'][] = $group->name . '-' . $mode;
		}
		return $controller;
	}
}