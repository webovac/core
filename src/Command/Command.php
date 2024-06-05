<?php

declare(strict_types=1);

namespace Webovac\Core\Command;


interface Command
{
	public function run(): int;
}