<?php

namespace Webovac\Core\Lib;

use Webovac\Core\Module;

class ModuleChecker
{
	private array $installedModules = [];

	/**
	 * @param Module[] $modules
	 */
	public function __construct(
		private array $modules,
	) {
		foreach ($this->modules as $module) {
			$this->installedModules[] = $module::getModuleName();
		}
	}


	public function isModuleInstalled(string $name)
	{
		return in_array($name, $this->installedModules, true);
	}
}