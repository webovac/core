<?php

declare(strict_types=1);

namespace Webovac\Core\Command;

use Stepapo\Manipulation\Lib\Processor;
use Stepapo\Utils\Command\Command;
use Webovac\Core\Module;


class ManipulationCommand implements Command
{
	/** @param Module[] $modules */
	public function __construct(
		private array $modules,
		private Processor $processor,
	) {}


	public function run(): int
	{
		$folders = [];
		$groups = [];
		foreach ($this->modules as $module) {
			$reflection = new \ReflectionClass($module);
			if (file_exists($dir = dirname($reflection->getFileName()) . "/manipulations")) {
				$folders[] = $dir;
			}
			if (!method_exists($module, 'getManipulationGroups')) {
				continue;
			}
			$groups = array_merge($groups, $module->getManipulationGroups());
		}
		$this->processor->process($folders, $groups);
		return 0;
	}
}