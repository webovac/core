<?php

declare(strict_types=1);

namespace Webovac\Core\Model;

use App\Model\Web\WebData;
use Nette\DI\Attributes\Inject;
use Nette\InvalidStateException;
use Nette\Utils\Arrays;
use Nextras\Dbal\Drivers\Exception\QueryException;
use Stepapo\Model\Orm\InternalRepository;
use Stepapo\Model\Orm\PrivateRepository;
use Stepapo\Model\Orm\StepapoRepository;
use Webovac\Core\Lib\Dir;
use Webovac\Core\Lib\FileUploader;


abstract class CmsRepository extends StepapoRepository
{
	#[Inject] public FileUploader $fileUploader;
	#[Inject] public Dir $dir;


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


	public function isForbiddenRepository(WebData $webData): bool
	{
		if ($this instanceof PrivateRepository) {
			return true;
		}
		if (!$webData->isAdmin && $this instanceof InternalRepository) {
			return true;
		}
		return false;
	}


	public function shouldFilterByWeb(WebData $webData): bool
	{
		return !$webData->isAdmin && method_exists($this, 'getFilterByWeb');
	}
}
