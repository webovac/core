<?php

namespace Webovac\Core\Command;


interface Command
{
	public function run(): int;
}