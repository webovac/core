<?php

namespace Webovac\Core\Command;

use Webovac\Core\Command\InstallCommand;


class MigrateAndInstallCommand implements Command
{
	public function __construct(
		private MigrateCommand $migrateCommand,
		private InstallCommand $installCommand,
	) {}


	public function run(): int
	{
		$this->migrateCommand->run();
		$this->installCommand->run();
		return 0;
	}
}