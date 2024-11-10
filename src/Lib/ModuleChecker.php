<?php

declare(strict_types=1);

namespace Webovac\Core\Lib;

use Stepapo\Utils\Service;
use Webovac\Core\Module;


class ModuleChecker implements Service
{
	private array $installedModules = [];


	/** @param Module[] $modules */
	public function __construct(
		private array $modules,
	) {
		foreach ($this->modules as $module) {
			$this->installedModules[] = $module::getModuleName();
		}
	}


	public function isModuleInstalled(string $name): bool
	{
		return in_array($name, $this->installedModules, true);
	}
}