<?php

declare(strict_types=1);

namespace Webovac\Core\Command;

use Nette\Utils\Arrays;
use Stepapo\Definition\Lib\Collector;
use Stepapo\Definition\Lib\EntityProcessor;
use Stepapo\Utils\Printer;
use Stepapo\Generator\Generator;
use Stepapo\Utils\Command\Command;
use Webovac\Core\Module;


class EntityCommand implements Command
{
	/** @param Module[] $modules */
	public function __construct(
		private array $modules,
		private EntityProcessor $processor,
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