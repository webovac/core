<?php

namespace Webovac\Core\Lib;

use App\Model\Orm;
use Nextras\Orm\Entity\Reflection\PropertyMetadata;
use Stepapo\Model\Orm\InternalProperty;
use Stepapo\Model\Orm\PrivateProperty;
use Stepapo\Utils\Service;


class PropertyChecker implements Service
{
	public function __construct(
		private DataProvider $dataProvider,
		private Orm $orm,
	) {}


	public function isForbiddenProperty(PropertyMetadata $propertyMetadata): bool
	{
		$webData = $this->dataProvider->getWebData();
		if (array_key_exists(PrivateProperty::class, $propertyMetadata->types)) {
			return true;
		}
		if (!$webData->isAdmin && array_key_exists(InternalProperty::class, $propertyMetadata->types)) {
			return true;
		}
		$repository = $propertyMetadata->relationship?->repository ? $this->orm->getRepository($propertyMetadata->relationship?->repository) : null;
		if ($repository?->isForbiddenRepository($webData)) {
			return true;
		}
		return false;
	}
}