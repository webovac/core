<?php

declare(strict_types=1);

namespace Webovac\Core\Command;

use Stepapo\Model\Definition\PropertyProcessor;
use Stepapo\Utils\Command\Command;
use Webovac\Core\Module;


class PropertyCommand implements Command
{
	/** @param Module[] $modules */
	public function __construct(
		private array $modules,
		private PropertyProcessor $processor,
	) {}


	public function run(): int
	{
		$folders = [];
		foreach ($this->modules as $module) {
			$reflection = new \ReflectionClass($module);
			if (file_exists($dir = dirname($reflection->getFileName()) . "/config/definitions")) {
				$folders[] = $dir;
			}
		}
		$this->processor->process($folders);
		return 0;
	}
}