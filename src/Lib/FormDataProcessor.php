<?php

namespace Webovac\Core\Lib;

use Nette\Forms\Container;
use Nette\Forms\Controls\BaseControl;


class FormDataProcessor
{
	/**
	 * Merges default data with form data
	 */
	public function process(Container $container, mixed $data, mixed $defaults): mixed
	{
		$components = $container->getComponents();
		$iterable = is_array($data) ? $data : $defaults;
		foreach ($iterable as $name => $value) {
			if (!array_key_exists($name, (array) $defaults)) {
				continue;
			}
			if (!array_key_exists($name, $components)
				|| (
					$components[$name] instanceof BaseControl
					&& $components[$name]->isOmitted()
				)
			) {
				$data[$name] = $defaults[$name];
			} else if ($components[$name] instanceof Container) {
				$data[$name] = $this->process($components[$name], $data[$name], $defaults[$name]);
			}
		}
		return $data;
	}
}