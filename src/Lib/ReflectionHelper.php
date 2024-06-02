<?php

namespace Webovac\Core\Lib;


class ReflectionHelper
{
	public static function propertyHasType(\ReflectionProperty $prop, string $t): bool
	{
		if ($prop->getType() instanceof \ReflectionNamedType) {
			return $prop->getType()->getName() === $t;
		} else {
			foreach ($prop->getType()->getTypes() as $type) {
				if ($type->getName() === $t) {
					return true;
				}
			}
		}
		return false;
	}
}