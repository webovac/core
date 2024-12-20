<?php

declare(strict_types=1);

namespace Webovac\Core\Lib;

use Nette\Neon\Neon;
use Nette\Utils\FileSystem;
use Nette\Utils\Finder;
use ReflectionClass;
use Stepapo\Utils\Service;
use Webovac\Core\Module;


class TestConfigProvider implements Service
{
	private array $paths = [];


	/** @param Module[] $modules */
	public function __construct(
		private array $modules,
		private Dir $dir,
	) {
		foreach ($modules as $module) {
			$reflection = new ReflectionClass($module);
			if (file_exists($path = dirname($reflection->getFileName()) . '/config/tests')) {
				if (str_contains($path, 'vendor')) {
					continue;
				}
				$this->paths[] = $path;
			}
		}
		if (file_exists($dir = $this->dir->getAppDir() . '/../config/tests')) {
			$this->paths[] = $dir;
		}
	}


	public function getTestConfigs(): array
	{
		$configs = [];
		foreach (Finder::findFiles('*.neon')->from($this->paths) as $file) {
			$config = (array) Neon::decode(FileSystem::read((string) $file));
			$configs[$config['name']] = $config;
		}
		$keys = array_keys($configs);
		shuffle($keys);
		$random = [];
		foreach ($keys as $key) {
			$random[$key] = $configs[$key];
		}
		return $random;
	}


	public function getCliSetups(): array
	{
		$setups = [];
		foreach ($this->modules as $module) {
			$reflection = new ReflectionClass($module);
			if (file_exists($path = dirname($reflection->getFileName()) . '/config/tests')) {
				if (str_contains($path, 'vendor')) {
					continue;
				}
				$setups[$reflection->getShortName()] = $module::getCliSetup();
			}
		}
		return $setups;
	}
}