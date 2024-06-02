<?php

namespace Webovac\Core\Model;

use Nette\Utils\ArrayHash;
use Webovac\Core\Lib\Collection;


class CmsData extends ArrayHash
{
	public function getCollection(string $name): Collection
	{
		$rf = new \ReflectionClass($this);
		$prop = $rf->getProperty($name);
		if (!property_exists($this, $name) || !$this->isCollection($name)) {
			throw new \InvalidArgumentException;
		}
		return new Collection($prop->isInitialized($this) ? $this->$name : []);
	}


	public function isCollection(string $name): bool
	{
		$rf = new \ReflectionClass($this);
		$prop = $rf->getProperty($name);
		if ($prop->getType() instanceof \ReflectionNamedType) {
			return $prop->getType()->getName() === 'array';
		} else {
			foreach ($prop->getType()->getTypes() as $type) {
				if ($type->getName() === 'array') {
					return true;
				}
			}
		}
		return false;
	}
}