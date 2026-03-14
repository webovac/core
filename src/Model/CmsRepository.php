<?php

declare(strict_types=1);

namespace Webovac\Core\Model;

use Build\Model\Web\WebData;
use Nette\DI\Attributes\Inject;
use Nette\InvalidStateException;
use Nette\Utils\Arrays;
use Nextras\Orm\Collection\ICollection;
use Stepapo\Model\Orm\InternalRepository;
use Stepapo\Model\Orm\PrivateRepository;
use Stepapo\Model\Orm\StepapoRepository;
use Webovac\Core\Lib\Dir;
use Webovac\Core\Lib\FileUploader;


abstract class CmsRepository extends StepapoRepository
{
	#[Inject] public FileUploader $fileUploader;
	#[Inject] public Dir $dir;


	public function getByParameters(?array $parameters = null, ?string $path = null, ?WebData $webData = null): ?CmsEntity
	{
		$filter = [
			ICollection::AND,
		];
		if ($webData && !$webData->isAdmin && $this instanceof HasWebFilter) {
			$filter[] = $this->getWebFilter($webData);
		}
		if ($parameters) {
			$filter[] = [$this->getKeyParameter() => Arrays::first($parameters)];
		} elseif ($path) {
			$filter[] = [$this->getKeyParameter() => Arrays::last(explode('/', $path))];
		} else {
			throw new InvalidStateException;
		}
		return $this->getBy($filter);
	}


	public function getEntityListByPath(string $path, ?WebData $webData = null): array
	{
		$filter = [];
		if ($webData && !$webData->isAdmin && $this instanceof HasWebFilter) {
			$filter[] = $this->findBy($this->getWebFilter($webData));
		}
		$filter[$this->getKeyParameter()] = explode('/', $path);
		return $this->findBy($filter)->fetchPairs('id');
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


	public function getKeyParameter(): string
	{
		return 'id';
	}


	public function prefix(string $prefix, array $filter): ?array
	{
		$result = [];
		foreach ($filter as $key => $value) {
			$result[is_numeric($key) ? $key : "$prefix->$key"] = is_array($value) ? $this->prefix($prefix, $value) : $value;
		}
		return $result;
	}
}
