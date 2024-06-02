<?php

namespace Webovac\Core\Lib;

use Nextras\Migrations\Controllers\ConsoleController;
use Nextras\Migrations\Drivers\PgSqlDriver;
use Nextras\Migrations\IDriver;
use Webovac\Core\Core;


class PrepareCoreMigrations implements PrepareMigrations
{
	public function __construct(private IDriver $driver)
	{}


	public function prepare(ConsoleController $controller): ConsoleController
	{
		$baseDir = __DIR__ . '/migrations';

		$db = $this->driver instanceof PgSqlDriver ? 'pgsql' : 'mysql';
		$controller->addGroup(Core::getModuleName(), "$baseDir/$db");
		$_SERVER['argv'][] = Core::getModuleName();

		return $controller;
	}
}