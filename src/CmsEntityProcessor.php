<?php

namespace Webovac\Core;

use App\Model\File\File;
use App\Model\File\FileData;
use App\Model\File\FileRepository;
use App\Model\Person\Person;
use Nextras\Orm\Entity\Reflection\PropertyMetadata;
use Nextras\Orm\Model\IModel;
use Nextras\Orm\Relationships\ManyHasMany;
use Nextras\Orm\Relationships\ManyHasOne;
use Nextras\Orm\Relationships\OneHasMany;
use Nextras\Orm\Relationships\OneHasOne;
use Webovac\Core\Model\CmsData;
use Webovac\Core\Model\CmsDataRepository;
use Webovac\Core\Model\CmsEntity;

class CmsEntityProcessor
{
	public bool $isModified = false;


	public function __construct(
		public CmsEntity $entity,
		private CmsData $data,
		private ?Person $person,
		private ?\DateTimeInterface $date,
		private string $mode,
		private IModel $model,
	) {}


	public function processEntity(?CmsEntity $parent = null, ?string $parentName = null)
	{
		$metadata = $this->entity->getMetadata();
		if ($parent && $parentName) {
			if (!isset($this->entity->$parentName) || $this->entity->$parentName !== $parent) {
				$this->entity->$parentName = $parent;
			}
		}
		foreach ($this->data as $name => $value) {
			if (in_array($name, ['createdAt', 'updatedAt', 'createdByPerson', 'updatedByPerson'], true)) {
				continue;
			}
			$property = $metadata->hasProperty($name) ? $metadata->getProperty($name) : null;
			if (!$property || $property->isPrimary) {
				continue;
			} elseif (!$property->wrapper) {
				$this->processScalar($property);
			} elseif (in_array($property->wrapper, [OneHasOne::class, ManyHasOne::class])) {
				$this->processHasOne($property);
			} elseif ($property->wrapper === ManyHasMany::class) {
				$this->processManyHasMany($property);
			}
		}
		$this->isModified = $this->entity->isModified();
		if (!$this->entity->isPersisted()) {
			if ($metadata->hasProperty('createdByPerson')) {
				$this->entity->createdByPerson = $this->person;
			}
			$this->model->persist($this->entity);
		}
		foreach ($this->data as $name => $value) {
			$property = $metadata->hasProperty($name) ? $metadata->getProperty($name) : null;
			if (!$property) {
				continue;
			} elseif ($property->wrapper === OneHasMany::class) {
				$this->processOneHasMany($property);
			}
		}

		if ($this->entity->isPersisted() && $this->isModified) {
			if ($metadata->hasProperty('updatedByPerson')) {
				$this->entity->updatedByPerson = $this->person;
			}
			if ($metadata->hasProperty('updatedAt')) {
				$this->entity->updatedAt = $this->date;
			}
			$this->model->persist($this->entity);
		}
	}


	public function processScalar(PropertyMetadata $property)
	{
		$name = $property->name;
		$value = $this->data->$name;
		if (!isset($this->entity->$name) || $this->entity->$name !== $value) {
			$this->entity->$name = $value;
		}
	}


	public function processHasOne(PropertyMetadata $property)
	{
		$name = $property->name;
		$relatedRepository = $this->model->getRepository($property->relationship->repository);
		if (isset($property->types[File::class])) {
			$value = $this->data->$name instanceof FileData
				? $relatedRepository->getById($this->data->id)
				: (
					$this->model->getRepository(FileRepository::class)->createFile($this->data->$name, $this->person, $name === 'iconFile')
						?: $this->entity->$name
				);
		} elseif ($this->data->$name instanceof CmsData) {
			$related = null;
			if (method_exists($relatedRepository, 'getByData')) {
				$related = $relatedRepository->getByData($this->data->$name);
			}
			if (!$related) {
				$related = $relatedRepository->createFromData($this->data->$name, person: $this->person, date: $this->date);
			}
			$value = $related;
		} elseif (is_numeric($this->data->$name)) {
			$value = $this->data->$name ? $relatedRepository->getById($this->data->$name) : null;
		} elseif (method_exists($relatedRepository, 'getByData')) {
			$related = $this->data->$name ? $relatedRepository->getByData($this->data->$name, $this->entity) : null;
			if (!$related && method_exists($relatedRepository, 'createFromString')) {
				$related = $relatedRepository->createFromString($this->data->$name);
			}
			$value = $related;
		}
		if (isset($value) && (!isset($this->entity->$name) || $this->entity->$name !== $value)) {
			$this->entity->$name = $value;
		}
	}


	public function processManyHasMany(PropertyMetadata $property)
	{
		$name = $property->name;
		$relatedRepository = $this->model->getRepository($property->relationship->repository);
		$array = [];
		foreach ($this->data->$name as $item) {
			if (isset($property->types[File::class])) {
				$array[] = $this->model->getRepository(FileRepository::class)->createFile($item, $this->person);
			} elseif (is_numeric($item)) {
				if ($item = $relatedRepository->getById($item)) {
					$array[] = $item;
				}
			} elseif (method_exists($relatedRepository, 'getByData')) {
				if ($item = $relatedRepository->getByData($item, $this->entity)) {
					$array[] = $item;
				}
			}
		}
		$oldIds = $this->entity->$name?->toCollection()->fetchPairs(null, 'id');
		$newIds = array_map(fn($v) => $v->id, $array);
		sort($oldIds);
		sort($newIds);
		if (!isset($this->entity->$name) || $oldIds !== $newIds) {
			$this->entity->$name->set($array);
		}
	}


	public function processOneHasMany(PropertyMetadata $property)
	{
		$name = $property->name;
		$ids = [];
		$relatedRepository = $this->model->getRepository($property->relationship->repository);
		$relatedClass = new \ReflectionClass($relatedRepository->getEntityClassName([]));
		foreach ($this->data->$name as $relatedData) {
			$relatedOriginal = method_exists($relatedRepository, 'getByData') ? $relatedRepository->getByData($relatedData, $this->entity) : null;
			$relatedEntity = $relatedOriginal ?: $relatedClass->newInstance();
			$processor = new self($relatedEntity, $relatedData, $this->person, $this->date, $this->mode, $this->model);
			$processor->processEntity(parent: $this->entity, parentName: $property->relationship->property);
			if (!$this->isModified) {
				$this->isModified = $processor->isModified;
			}
			$ids[] = $relatedEntity->getPersistedId();
		}
		if ($this->mode === CmsDataRepository::MODE_INSTALL) {
			foreach ($this->entity->$name as $related) {
				if (!in_array($related->getPersistedId(), $ids, true)) {
					$this->isModified = true;
					$relatedRepository->delete($related);
				}
			}
		}
	}
}