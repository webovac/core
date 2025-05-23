<?php

declare(strict_types=1);

namespace Webovac\Core\Model;

use App\Lib\OrmFunctions;
use App\Model\Web\WebData;
use Nette\DI\Attributes\Inject;
use Nette\InvalidStateException;
use Nette\Utils\Arrays;
use Nextras\Dbal\Drivers\Exception\QueryException;
use Nextras\Orm\Collection\Functions\CollectionFunction;
use Nextras\Orm\Entity\IEntity;
use Nextras\Orm\StorageReflection\StringHelper;
use Stepapo\Model\Orm\StepapoRepository;
use Stepapo\Utils\Injectable;
use Webovac\Core\Lib\Dir;
use Webovac\Core\Lib\FileUploader;


abstract class CmsRepository extends StepapoRepository implements Injectable
{
	#[Inject] public FileUploader $fileUploader;
	#[Inject] public Dir $dir;
	#[Inject] public OrmFunctions $functions;


	public function createCollectionFunction(string $name): CollectionFunction
	{
		$constName = strtoupper(StringHelper::underscore($name));
		if (defined("App\\Lib\\OrmFunctions::$constName")) {
			return $this->functions->call($name);
		} else {
			return parent::createCollectionFunction($name);
		}
	}


	public function getById($id): ?CmsEntity
	{
		try {
			return parent::getById($id);
		} catch (QueryException $e) {
			return null;
		}
	}


	public function getByParameters(?array $parameters = null, ?string $path = null, ?WebData $webData = null): ?CmsEntity
	{
		if ($parameters) {
			return $this->getById(Arrays::first($parameters));
		} elseif ($path) {
			return $this->getById(Arrays::last(explode('/', $path)));
		}
		throw new InvalidStateException;
	}


	public function getEntityListByPath(string $path, ?WebData $webData = null): array
	{
		return $this->findBy(['id' => explode('/', $path)])->fetchPairs('id');
	}


	public function delete(CmsEntity $entity): void
	{
		$this->onBeforeRemove($entity);
		$this->mapper->delete($entity);
		$this->onAfterRemove($entity);
	}
}
