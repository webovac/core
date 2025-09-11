<?php

namespace Webovac\Core\Lib;

use Nextras\Orm\Entity\Reflection\PropertyMetadata;
use Stepapo\Model\Orm\InternalProperty;
use Stepapo\Model\Orm\PrivateProperty;
use Stepapo\Utils\Service;


class PropertyChecker implements Service
{
	public function __construct(
		private DataProvider $dataProvider,
	) {}


	public function isForbiddenProperty(PropertyMetadata $propertyMetadata): bool
	{
		if (array_key_exists(PrivateProperty::class, $propertyMetadata->types)) {
			return true;
		}
		if (!$this->dataProvider->getWebData()->isAdmin && array_key_exists(InternalProperty::class, $propertyMetadata->types)) {
			return true;
		}
		return false;
	}
}