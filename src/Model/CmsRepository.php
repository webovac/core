<?php

declare(strict_types=1);

namespace Webovac\Core\Model;

use App\Lib\OrmFunctions;
use App\Model\File\File;
use App\Model\File\FileData;
use App\Model\File\FileRepository;
use App\Model\Person\Person;
use DateTimeInterface;
use Nette\DI\Attributes\Inject;
use Nextras\Orm\Entity\ToArrayConverter;
use Nextras\Orm\Relationships\ManyHasMany;
use Nextras\Orm\Relationships\ManyHasOne;
use Nextras\Orm\Relationships\OneHasMany;
use Nextras\Orm\Relationships\OneHasOne;
use Nextras\Orm\Repository\Repository;
use Nextras\Orm\StorageReflection\StringHelper;
use ReflectionClass;
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
		CmsData $data,
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
		$old = $original?->toArray(ToArrayConverter::RELATIONSHIP_AS_ID);
		$class = new ReflectionClass($this->getEntityClassName([]));
		$entity = $original ?: $class->newInstance();
		$metadata = $entity->getMetadata();
		foreach ($data as $name => $value) {
			if (in_array($name, ['createdAt', 'updatedAt', 'createdByPerson', 'updatedByPerson'], true)) {
				continue;
			}
			$property = $metadata->hasProperty($name) ? $metadata->getProperty($name) : null;
			if (!$property || $property->isPrimary) {
				continue;
			} elseif (!$property->wrapper) {
				$entity->$name = $data->$name;
			} elseif (in_array($property->wrapper, [OneHasOne::class, ManyHasOne::class])) {
				$relatedRepository = $this->getModel()->getRepository($property->relationship->repository);
				if (isset($property->types[File::class])) {
					$entity->$name = $data->$name instanceof FileData ? $relatedRepository->getById($data->id) : ($this->getModel()->getRepository(FileRepository::class)->createFile($data->$name, $person, $name === 'iconFile') ?: $entity->$name);
				} elseif ($data->$name instanceof CmsData) {
					$related = null;
					if (method_exists($relatedRepository, 'getByData')) {
						$related = $relatedRepository->getByData($data->$name);
					}
					if (!$related) {
						$related = $relatedRepository->createFromData($data->$name, person: $person);
					}
					$entity->$name = $related;
				} elseif (is_numeric($data->$name)) {
					$entity->$name = $data->$name ? $relatedRepository->getById($data->$name) : null;
				} elseif (method_exists($relatedRepository, 'getByData')) {
					$related = $data->$name ? $relatedRepository->getByData($data->$name, $entity) : null;
					if (!$related && method_exists($relatedRepository, 'createFromString')) {
						$related = $relatedRepository->createFromString($data->$name);
					}
					$entity->$name = $related;
				}
			} elseif ($property->wrapper === ManyHasMany::class) {
				$relatedRepository = $this->getModel()->getRepository($property->relationship->repository);
				$array = [];
				foreach ($data->$name as $item) {
					if (isset($property->types[File::class])) {
						$array[] = $this->getModel()->getRepository(FileRepository::class)->createFile($item, $person);
					} elseif (is_numeric($item)) {
						if ($item = $relatedRepository->getById($item)) {
							$array[] = $item;
						}
					} elseif (method_exists($relatedRepository, 'getByData')) {
						if ($item = $relatedRepository->getByData($item, $entity)) {
							$array[] = $item;
						}
					}
				}
				$entity->$name->set($array);
			}
		}
		if ($parent && $parentName) {
			$entity->$parentName = $parent;
		}
		$isChanged = $entity->isChanged($old);
		if (!$original) {
			if ($metadata->hasProperty('createdByPerson')) {
				$entity->createdByPerson = $person;
			}
			$this->persist($entity);
		}
		foreach ($data as $name => $value) {
			$property = $metadata->hasProperty($name) ? $metadata->getProperty($name) : null;
			if (!$property) {
				continue;
			} elseif ($property->wrapper === OneHasMany::class) {
				$ids = [];
				$relatedRepository = $this->getModel()->getRepository($property->relationship->repository);
				foreach ($data->$name as $relatedData) {
					$originalRelated = method_exists($relatedRepository, 'getByData') ? $relatedRepository->getByData($relatedData, $entity) : null;
					if (!$isChanged) {
						$oldRelated = $originalRelated?->toArray(ToArrayConverter::RELATIONSHIP_AS_ID);
					}
					$related = $relatedRepository->createFromData($relatedData, $originalRelated, $entity, $property->relationship->property, person: $person, date: $date);
					if (!$isChanged) {
						$isChanged = $related->isChanged($oldRelated);
					}
					$ids[] = $related->getPersistedId();
				}
				/** Promazat zrušené entity */
				if ($original && $mode === CmsDataRepository::MODE_INSTALL) {
					foreach ($entity->$name as $related) {
						if (!in_array($related->getPersistedId(), $ids, true)) {
							$relatedRepository->delete($related);
						}
					}
				}
			}
		}
		if ($original && $isChanged) {
			if ($metadata->hasProperty('updatedByPerson')) {
				$entity->updatedByPerson = $person;
			}
			if ($metadata->hasProperty('updatedAt')) {
				$entity->updatedAt = $date;
			}
			$this->persist($entity);
		}
		return $entity;
	}
}
