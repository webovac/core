<?php

declare(strict_types=1);

namespace Webovac\Core\Lib;

use Build\Model\Module\Module;
use Build\Model\Orm;
use Build\Model\Web\Web;
use Nette\InvalidStateException;
use Stepapo\Utils\Clearable;
use Stepapo\Utils\Service;
use Webovac\Core\HasModuleSetups;


class ModuleRequirementChecker implements Service, Clearable
{
	private array $checked = [];
	private array $setups = [];


	/** @param HasModuleSetups[] $hasModuleSetups */
	public function __construct(
		private array $hasModuleSetups,
		private Orm $orm,
		private CmsUser $cmsUser,
	) {
		foreach ($this->hasModuleSetups as $hasModuleSetup) {
			foreach ($hasModuleSetup->getModuleSetups() as $name => $setup) {
				if (isset($this->setups[$name])) {
					throw new InvalidStateException("Duplicate module setup for '$name'.");
				}
				$this->setups[$name] = $setup;
			}
		}
	}


	public function isModuleInstallable(Module $module, Web $web): bool
	{
		if (array_key_exists($module->name, $this->checked)) {
			return $this->checked[$module->name];
		}
		if (isset($this->setups[$module->name])) {
			$return = ($this->setups[$module->name])($this->orm, $this->cmsUser, $web);
			$this->checked[$module->name] = $return;
			return $return;
		}
		$this->checked[$module->name] = true;
		return true;
	}


	/**
	 * @param array $modules
	 * @return array
	 */
	public function filterModules(array $modules, Web $web): array
	{
		$filteredModules = [];
		foreach ($modules as $module) {
			if (!$this->isModuleInstallable($module, $web)) {
				continue;
			}
			$filteredModules[] = $module;
		}
		return $filteredModules;
	}


	public function clear(): void
	{
		$this->checked = [];
	}
}
