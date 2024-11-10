<?php

declare(strict_types=1);

namespace Webovac\Core\Model;

use App\Model\Orm;
use Nette\Caching\Cache;
use Nextras\Orm\Repository\IRepository;
use Stepapo\Utils\Injectable;
use Stepapo\Model\Data\Collection;
use Stepapo\Model\Data\Item;
use Stepapo\Model\Data\Repository;
use Stepapo\Utils\Service;
use Throwable;
use Webovac\Core\Lib\ModuleChecker;


class CmsDataRepository extends Repository implements Service, Injectable
{
	public function __construct(
		protected Orm $orm,
		protected ModuleChecker $moduleChecker,
		protected Cache $cache,
	) {}


	/**
	 * @return Collection<Item>
	 * @throws Throwable
	 */
	protected function getCollection(): Collection
	{
		if (!isset($this->collection)) {
			$this->collection = $this->cache->load(lcfirst($this->getName()), function () {
				$collection = new Collection;
				foreach ($this->getOrmRepository()->findAll() as $entity) {
					$collection[$entity->getPersistedId()] = $entity->getData();
				}
				return $collection;
			});
		}
		return $this->collection;
	}


	protected function getName(): string
	{
		$className = preg_replace('~^.+\\\\~', '', get_class($this));
		assert($className !== null);
		return str_replace('DataRepository', '', $className);
	}


	protected function getOrmRepository(): IRepository
	{
		$name = $this->getName();
		return $this->orm->getRepository("App\\Model\\$name\\{$name}Repository");
	}
}