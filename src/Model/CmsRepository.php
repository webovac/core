<?php

declare(strict_types=1);

namespace Webovac\Core\Model;

use App\Lib\OrmFunctions;
use App\Model\Person\Person;
use DateTimeInterface;
use Nette\DI\Attributes\Inject;
use Nextras\Orm\Repository\Repository;
use Nextras\Orm\StorageReflection\StringHelper;
use ReflectionClass;
use Stepapo\Utils\Model\Item;
use Webovac\Core\CmsEntityProcessor;
use Webovac\Core\Lib\Dir;
use Webovac\Core\Lib\FileUploader;


abstract class CmsRepository extends Repository
{
	#[Inject] public FileUploader $fileUploader;
	#[Inject] public Dir $dir;
	#[Inject] public OrmFunctions $functions;


	public function createCollectionFunction(string $name): mixed
	{
		$constName = strtoupper(StringHelper::underscore($name));
		if (defined("App\\Lib\\OrmFunctions::$constName")) {
			return $this->functions->call($name);
		} else {
			return parent::createCollectionFunction($name);
		}
	}


	public function getByParameter(mixed $parameter): ?CmsEntity
	{
		return $this->getBy(['id' => $parameter]);
	}


	public function delete(CmsEntity $entity): void
	{
		$this->mapper->delete($entity);
	}


	public function createFromData(
		Item $data,
		?CmsEntity $original = null,
		?CmsEntity $parent = null,
		?string $parentName = null,
		?Person $person = null,
		?DateTimeInterface $date = null,
		string $mode = CmsDataRepository::MODE_INSTALL,
		bool $getOriginalByData = false,
	): CmsEntity
	{
		if ($getOriginalByData) {
			$original ??= method_exists($this, 'getByData') ? $this->getByData($data, $parent) : null;
		}
		$class = new ReflectionClass($this->getEntityClassName([]));
		$entity = $original ?: $class->newInstance();
		$processor = new CmsEntityProcessor($entity, $data, $person, $date, $mode, $this->getModel());
		$processor->processEntity($parent, $parentName);
		return $entity;
	}
}
