<?php

declare(strict_types=1);

namespace Webovac\Core\Lib;

use Nette\Utils\Arrays;
use Stepapo\Utils\Service;
use Webovac\Core\Attribute\RequiresEntity;
use Webovac\Core\MainModuleControl;


class ComponentProvider implements Service
{
	private array $components;


	public function getComponents(): array
	{
		if (!isset($this->components)) {
			$mainModuleControls = array_filter(
				get_declared_classes(),
				fn($className) => in_array(MainModuleControl::class, class_implements($className)),
			);
			foreach ($mainModuleControls as $mainModuleControl) {
				$this->addComponents($mainModuleControl);
			}
		}
		return $this->components;
	}


	private function addComponents(string $className): void
	{
		foreach ($this->getComponentList($className) as $key => $value) {
			$parts = explode('\\', $className);
			$module = lcfirst(str_replace('Control', '', Arrays::last($parts)));
			$this->components[] = [
				'name' => $module . '-' . (is_numeric($key) ? $value : $key),
				'requires' => is_numeric($key) ? null : Arrays::last(explode('\\', $value)),
			];
		}
	}


	/**
	 * @throws ReflectionException
	 */
	private function getComponentList(string $className): array
	{
		$return = [];
		$rf = new \ReflectionClass($className);
		foreach ($rf->getMethods() as $method) {
			preg_match('/createComponent(.+)/', $method->getName(), $m);
			if (!isset($m[1])) {
				continue;
			}
			if ($ar = $method->getAttributes(RequiresEntity::class)) {
				$return[lcfirst($m[1])] = $ar[0]->getArguments()[0];
			} else {
				$return[] = lcfirst($m[1]);
			}
		}
		return $return;
	}
}