<?php

namespace Webovac\Core\Lib;

use Nextras\Migrations\Controllers\ConsoleController;


interface PrepareMigrations
{
	public function prepare(ConsoleController $controller): ConsoleController;
}
