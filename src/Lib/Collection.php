<?php

namespace Webovac\Core\Lib;

use Webovac\Core\Model\CmsData;


class Collection extends \ArrayObject
{
	public function findAll(): Collection
	{
		return $this;
	}


	public function findBy(array $conds): Collection
	{
		return new Collection(array_filter(
			(array) $this,
			function (CmsData $entity) use ($conds) {
				foreach ($conds as $property => $value) {
					if (!property_exists($entity, $property)) {
						throw new \InvalidArgumentException;
					}
					if ($entity->$property !== $value) {
						return false;
					}
				}
				return true;
			}
		));
	}


	public function getById(mixed $id): ?CmsData
	{
		return $this[$id] ?? null;
	}


	public function getBy(array $conds): ?CmsData
	{
		return current((array) $this->findBy($conds)) ?: null;
	}
}