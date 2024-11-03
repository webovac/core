<?php

declare(strict_types=1);

namespace Webovac\Core\Command;

use Stepapo\Definition\Lib\Analyzer;
use Stepapo\Definition\Lib\Collector;
use Stepapo\Definition\Lib\Comparator;
use Stepapo\Definition\Lib\DbProcessor;
use Stepapo\Utils\Command\Command;
use Webovac\Core\Module;


class DefinitionCommand implements Command
{
	/** @param Module[] $modules */
	public function __construct(
		private array $modules,
		private DbProcessor $processor,
	) {}


	public function run(): int
	{
		$folders = [];
		foreach ($this->modules as $module) {
			$reflection = new \ReflectionClass($module);
			if (file_exists($dir = dirname($reflection->getFileName()) . "/definitions")) {
				$folders[] = $dir;
			}
		}
		$this->processor->process($folders);
		return 0;
	}
}