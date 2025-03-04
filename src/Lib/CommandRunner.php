<?php

declare(strict_types=1);

namespace Webovac\Core\Lib;

use Nette\InvalidArgumentException;
use Nette\Utils\Arrays;
use Stepapo\Utils\Command\Command;
use Stepapo\Utils\Service;


class CommandRunner implements Service
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


	public function run(string $name): int
	{
		if (!str_contains($name, ':')) {
			throw new InvalidArgumentException("Invalid command.");
		}
		[$module, $command] = explode(':', $name);
		if (!isset($this->commands[$module][$command])) {
			throw new InvalidArgumentException("Command '$command' does not exist in module '$module'.");
		}
		return $this->commands[$module][$command]->run();
	}


	public function printCommands(): void
	{
		foreach ($this->commands as $module => $moduleCommands) {
			foreach ($moduleCommands as $name => $command) {
				print "$module:$name\n";
			}
		}
	}
}