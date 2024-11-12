<?php

declare(strict_types=1);

namespace Webovac\Core\Ext\Migrations;

use Nextras\Migrations\Controllers\ConsoleController;
use Nextras\Migrations\IDriver;
use Stepapo\Model\MigrationGroup;
use Stepapo\Utils\Printer;


class CmsConsoleController extends ConsoleController
{
	private Printer $printer;


	public function __construct(IDriver $driver)
	{
		parent::__construct($driver);
		$printer = $this->createPrinter();
		$this->runner = new CmsRunner($driver, $printer);
		$this->printer = new Printer;
	}


	public function run(): void
	{
		$this->printHeader();
		$this->registerGroups();
		$this->runner->run($this->mode);
	}


	/** PRIVATE PARENT */
	private function printHeader(): void
	{
		$this->printer->printBigSeparator();
		$this->printer->printLine("Migrations", "aqua");
		$this->printer->printSeparator();
	}


	/**
	 * @param  list<string>  $dependencies
	 */
	public function addCmsGroup(string $name, MigrationGroup $migrationGroup, array $files, array $dependencies = []): self
	{
		$group = new CmsGroup;
		$group->name = $name;
		$group->migrationGroup = $migrationGroup;
		$group->files = $files;
		$group->dependencies = $dependencies;
		$group->enabled = false;
		$this->groups[$name] = $group;
		return $this;
	}
}