<?php

declare(strict_types=1);

namespace Webovac\Core\Lib;

use Nette\InvalidArgumentException;
use Nette\Utils\Arrays;
use Webovac\Core\Command\Command;


class CommandRunner
{
	/** @var Command[][] */ private array $commands;


	/** @param Command[] $commands */
	public function __construct(array $commands)
	{
		foreach ($commands as $command) {
			$className = get_class($command);
			$parts = explode('\\', $className);
			$lastKey = Arrays::lastKey($parts);
			$name = $parts[$lastKey];
			$module = $parts[$lastKey - 2];
			$this->commands[lcfirst($module)][lcfirst(str_replace('Command', '', $name))] = $command;
		}
	}


	public function run(string $module, string $name): int
	{
		if (!isset($this->commands[$module][$name])) {
			throw new InvalidArgumentException("Command '$name' does not exist in module '$module'.");
		}
		return $this->commands[$module][$name]->run();
	}
}