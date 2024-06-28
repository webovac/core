<?php

declare(strict_types=1);

namespace Webovac\Core\Model;

use App\Model\Orm;
use Nette\Caching\Cache;
use Nette\Schema\Processor;
use Nette\Schema\Schema;
use Nextras\Orm\Repository\IRepository;
use Webovac\Core\Lib\CmsExpect;
use Webovac\Core\Lib\Collection;
use Webovac\Core\Lib\ModuleChecker;


abstract class CmsDataRepository
{
	public const string MODE_INSTALL = 'install';
	public const string MODE_UPDATE = 'update';
	/** @var Collection<CmsData> */ protected Collection $collection;
	protected Processor $processor;


	public function __construct(
		protected Orm $orm,
		protected ModuleChecker $moduleChecker,
		protected Cache $cache,
	) {
		$this->processor = new Processor;
	}


	/** @return Collection<CmsData> */
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


//	public function createFromConfig(array $config, string $mode = self::MODE_INSTALL): CmsEntity
//	{
//		$data = $this->createDataFromConfig($config, $mode);
//		$entity = $this->getOrmRepository()->createFromData($data, mode: $mode, getOriginalByData: true);
//		if (method_exists($this->getOrmRepository(), 'postProcessFromData')) {
//			$this->getOrmRepository()->postProcessFromData($data, $entity, mode: $mode);
//		}
//		return $entity;
//	}
//
//
//	public function createDataFromConfig(array $config, string $mode): CmsData
//	{
//		$schema = $this->getSchema($mode);
//		if (!$schema) {
//			throw new InvalidArgumentException();
//		}
//		return $this->processor->process($schema, $config);
//	}


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


	protected function getDataClass(): ?string
	{
		$name = $this->getName();
		$className = "App\\Model\\$name\\{$name}Data";
		return class_exists($className) ? $className : null;
	}


	protected function getSchema(string $mode): ?Schema
	{
		$dataClass = $this->getDataClass();
		return $dataClass ? CmsExpect::fromDataClass($dataClass, $mode) : null;
	}


	/** @return Collection<CmsData> */ 
	public function findAll(): Collection
	{
		return $this->getCollection()->findAll();
	}


	/** @return Collection<CmsData> */ 
	public function findBy(array $conds): Collection
	{
		return $this->getCollection()->findBy($conds);
	}


	public function getById(mixed $id): ?CmsData
	{
		return $this->getCollection()->getById($id);
	}


	public function getBy(array $conds): ?CmsData
	{
		return $this->getCollection()->getBy($conds);
	}
}