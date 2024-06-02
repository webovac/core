<?php

namespace Webovac\Core\Model;

use App\Model\Language\LanguageData;
use Nette\Caching\Cache;
use Nette\Utils\Type;
use Nextras\Orm\Entity\Entity;
use Nextras\Orm\Entity\ToArrayConverter;
use Nextras\Orm\Relationships\ManyHasMany;
use Nextras\Orm\Relationships\ManyHasOne;
use Nextras\Orm\Relationships\OneHasMany;
use Nextras\Orm\Relationships\OneHasOne;


abstract class CmsEntity extends Entity
{
	public Cache $cache;


	public function injectCache(Cache $cache): void
	{
		$this->cache = $cache;
	}


	protected function getCache(): Cache
	{
		return $this->cache;
	}


	abstract public function getDataClass(): string;


	public function getParameter(?LanguageData $language = null): mixed
	{
		return $this->getPersistedId();
	}


	public function getParentParameter(?LanguageData $language = null): mixed
	{
		return null;
	}


	public function isChanged(array $old): bool
	{
		$new = $this->toArray(ToArrayConverter::RELATIONSHIP_AS_ID);
		foreach ($old as $key => $value) {
			if ($value instanceof \DateTimeImmutable) {
				if ($value != $new[$key]) {
					return true;
				}
				continue;
			}
			if ($value !== $new[$key]) {
				return true;
			}
		}
		return false;
	}


	public function getData(): CmsData
	{
		$class = new \ReflectionClass($this->getDataClass());
		$data = $class->newInstance();
		foreach ($class->getProperties() as $p) {
			$name = $p->name;
			$property = $this->getMetadata()->hasProperty($name) ? $this->getMetadata()->getProperty($name) : null;
			if (!$property) {
				continue;
			} elseif (!$property->wrapper) {
				$data->$name = $this->$name;
			} elseif (in_array($property->wrapper, [OneHasOne::class, ManyHasOne::class])) {
				$data->$name = $this->shouldGetData($p) ? $this->$name?->getData() : $this->$name?->getPersistedId();
			} elseif ($property->wrapper === OneHasMany::class) {
				foreach ($this->$name as $related) {
					$data->$name[$related->getPersistedId()] = $related->getData();
				}
			} elseif ($property->wrapper === ManyHasMany::class) {
				foreach ($this->$name as $related) {
					$data->$name[] = $related->getPersistedId();
				}
			}
		}
		return $data;
	}


	private function shouldGetData(\ReflectionProperty $property): bool
	{
		$types = Type::fromReflection($property)->getTypes();
		foreach ($types as $type) {
			if (!$type->isClass()) {
				continue;
			}
			if ((new \ReflectionClass($type->getSingleName()))->isSubclassOf(CmsData::class)) {
				return true;
			}
		}
		return false;
	}
}
