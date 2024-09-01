<?php

declare(strict_types=1);

namespace Webovac\Core\Model;

use DateTimeImmutable;
use Nette\NotSupportedException;
use Nette\Utils\Type;
use Nextras\Orm\Entity\Entity;
use Nextras\Orm\Entity\ToArrayConverter;
use Nextras\Orm\Relationships\ManyHasMany;
use Nextras\Orm\Relationships\ManyHasOne;
use Nextras\Orm\Relationships\OneHasMany;
use Nextras\Orm\Relationships\OneHasOne;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use Stepapo\Utils\Model\Item;
use Webovac\Core\Lib\DataProvider;


abstract class CmsEntity extends Entity
{
	public const OMITTED_PROPERTIES = ['id', 'createdByPerson', 'updatedByPerson', 'createdAt', 'updatedAt'];
	protected DataProvider $dataProvider;


	public function injectDataProvider(DataProvider $dataProvider): void
	{
		$this->dataProvider = $dataProvider;
	}


	/**
	 * @throws ReflectionException
	 */
	public function getData(bool $neon = false): Item
	{
		if (!method_exists($this, 'getDataClass')) {
			throw new NotSupportedException;
		}
		$class = new ReflectionClass($this->getDataClass());
		$data = $class->newInstance();
		foreach ($class->getProperties() as $p) {
			$name = $p->name;
			if ($neon && in_array($name, self::OMITTED_PROPERTIES, true)) {
				continue;
			}
			$property = $this->getMetadata()->hasProperty($name) ? $this->getMetadata()->getProperty($name) : null;
			if (!$property) {
				continue;
			} elseif (!$property->wrapper) {
				$data->$name = $this->$name;
			} elseif (in_array($property->wrapper, [OneHasOne::class, ManyHasOne::class])) {
				$data->$name = $this->shouldGetData($p) ? $this->$name?->getData($neon) : $this->$name?->getPersistedId();
			} elseif ($property->wrapper === OneHasMany::class) {
				foreach ($this->$name as $related) {
					$data->$name[$related->getPersistedId()] = $related->getData($neon);
				}
			} elseif ($property->wrapper === ManyHasMany::class) {
				foreach ($this->$name as $related) {
					$data->$name[] = $related->getPersistedId();
				}
			}
		}
		return $data;
	}


	/**
	 * @throws ReflectionException
	 */
	private function shouldGetData(ReflectionProperty $property): bool
	{
		$types = Type::fromReflection($property)->getTypes();
		foreach ($types as $type) {
			if (!$type->isClass()) {
				continue;
			}
			if ((new ReflectionClass($type->getSingleName()))->isSubclassOf(Item::class)) {
				return true;
			}
		}
		return false;
	}
}
