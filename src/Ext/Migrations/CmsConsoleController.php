<?php

declare(strict_types=1);

namespace Webovac\Core\Ext\Migrations;

use Nextras\Migrations\Controllers\ConsoleController;
use Nextras\Migrations\Engine\Runner;
use Nextras\Migrations\IDriver;
use Webovac\Core\MigrationGroup;


class CmsConsoleController extends ConsoleController
{
	public function __construct(IDriver $driver)
	{
		parent::__construct($driver);
		$printer = $this->createPrinter();
		$this->runner = new CmsRunner($driver, $printer);
	}


	public function run(): void
	{
		$this->processArguments();
		$this->printHeader();
		$this->registerGroups();
		$this->runner->run($this->mode);
	}


	/** PRIVATE PARENT */
	private function printHeader(): void
	{
		if ($this->mode === Runner::MODE_INIT) {
			printf("-- Migrations init\n");
		} else {
			printf("Migrations\n");
			printf("------------------------------------------------------------\n");
		}
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


	/** PRIVATE PARENT */
	private function processArguments(): void
	{
		$arguments = array_slice($_SERVER['argv'], 1);
		$help = count($arguments) === 0;
		$groups = $error = false;

		foreach ($arguments as $argument) {
			if (strncmp($argument, '--', 2) === 0) {
				if ($argument === '--reset') {
					$this->mode = Runner::MODE_RESET;
				} elseif ($argument === '--init-sql') {
					$this->mode = Runner::MODE_INIT;
				} elseif ($argument === '--help') {
					$help = true;
				} else {
					fprintf(STDERR, "Warning: Unknown option '%s'\n", $argument);
					continue;
				}
			} else {
				if (isset($this->groups[$argument])) {
					$this->groups[$argument]->enabled = true;
					$groups = true;
				}
			}
		}

		if (!$groups && !$help) {
			fprintf(STDERR, "Error: At least one group must be enabled.\n");
			$error = true;
		}

		if ($error) {
			printf("\n");
		}

		if ($help || $error) {
			printf("Usage: %s group1 [, group2, ...] [--reset] [--help]\n", basename($_SERVER['argv'][0]));
			printf("Registered groups:\n");
			foreach (array_keys($this->groups) as $group) {
				printf("  %s\n", $group);
			}
			printf("\nSwitches:\n");
			printf("  --reset      drop all tables and views in database and start from scratch\n");
			printf("  --init-sql   prints initialization sql for all present migrations\n");
			printf("  --help       show this help\n");
			exit(intval($error));
		}
	}
}