<?php

declare(strict_types=1);

namespace Webovac\Core\Command;

use ReflectionClass;
use Stepapo\Utils\Command\Command;
use Webovac\Core\Lib\Dir;
use Webovac\Core\Module as WebovacModule;
use Webovac\Generator\Lib\Processor;


class GenerateCommand implements Command
{
	public array $folders;


	/** @param WebovacModule[] $modules */
	public function __construct(
		private Dir $dir,
		private array $modules,
		private Processor $processor,
	) {
		foreach ($this->modules as $module) {
			$reflection = new ReflectionClass($module);
			if (file_exists($dir = dirname($reflection->getFileName()) . '/files')) {
				$this->folders[] = $dir;
			}
		}
		if (file_exists($dir = $this->dir->getAppDir() . '/files')) {
			$this->folders[] = $dir;
		}
	}


	public function run(): int
	{
		$this->processor->process($this->folders, $this->dir->getAppDir());
		return 0;
	}
}