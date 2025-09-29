<?php

declare(strict_types=1);

namespace Webovac\Core\Lib;

use Nette\Schema\ValidationException;
use Nextras\Orm\Collection\Functions\ConjunctionOperatorFunction;
use Nextras\Orm\Collection\Functions\DisjunctionOperatorFunction;
use Nextras\Orm\Collection\ICollection;
use Nextras\Orm\Entity\IEntity;
use Nextras\Orm\Exception\InvalidArgumentException;
use Stepapo\Model\Orm\StepapoEntity;
use Stepapo\Model\Orm\ToArrayConverter;
use Stepapo\Restful\Application\BadRequestException;
use Stepapo\Restful\ConvertedResource;
use Stepapo\Restful\Converters\DateTimeConverter;
use Stepapo\Restful\Converters\ObjectConverter;
use Stepapo\Restful\Converters\ResourceConverter;
use Stepapo\Utils\Service;


class ResourceGenerator implements Service
{
	public function createFromArrayQuery(ICollection|IEntity $collection, ?array $params = null, callable|null $checkProperty = null, bool $showCompleteRelations = false)
	{
		try {
			return $this->create($collection, Query::createFromArray($params ?? []), $checkProperty, $showCompleteRelations);
		} catch (ValidationException|InvalidArgumentException $e) {
			throw $e;
		} catch (\Throwable $e) {
			throw new BadRequestException('Invalid query', $e->getCode(), $e);
		}
	}


	public function create(ICollection|IEntity $data, ?Query $query = null, callable|null $checkProperty = null, bool $showCompleteRelations = false)
	{
		if ($data instanceof ICollection) {
			$filter = $this->prepareFilter($query->where);
			$sort = $this->prepareSort($query->order);
			$items = $data->findBy($filter);
			foreach ($sort as $s) {
				$items = $items->orderBy($s['expression'], $s['direction']);
			}
			$count = $items->countStored();
			$items = $items
				->orderBy('id')
				->limitBy($query->limit, $query->offset);
			$resource = [];
			$resource['count'] = $count;
			$resource['limit'] = $query->limit;
			$resource['page'] = $query->page;

			/** @var StepapoEntity $item */
			foreach ($items as $item) {
				$resource['items'][$item->getPersistedId()] = $item->toArray(
					mode: $showCompleteRelations ? ToArrayConverter::RELATIONSHIP_AS_ARRAY : ToArrayConverter::RELATIONSHIP_AS_ID,
					select: $query->select,
					checkProperty: $checkProperty
				);
			}
		} else {
			$resource = $data->toArray(
				mode: $showCompleteRelations ? ToArrayConverter::RELATIONSHIP_AS_ARRAY : ToArrayConverter::RELATIONSHIP_AS_ID,
				select: $query->select,
				checkProperty: $checkProperty
			);
		}
		$converter = new ResourceConverter;
		$converter->addConverter(new ObjectConverter);
		$converter->addConverter(new DateTimeConverter);
		return new ConvertedResource($converter, (array) $resource);
	}


	private function prepareFilter(?array $filter): array
	{
		$preparedFilter = [];
		foreach ((array) $filter as $condition => $value) {
			if ($condition === 0) {
				$value = match($value) {
					'and' => ConjunctionOperatorFunction::class,
					'or' => DisjunctionOperatorFunction::class,
					default => $value
				};
			}
			if ($condition === 1) {
				if (is_array($value)) {
					$value = array_map(fn($v) => $this->prepareColumn($v), $value);
				} else {
					$value = $this->prepareColumn($value);
				}
			}
			$preparedFilter[$this->prepareColumn($condition)] = is_array($value)
				? $this->prepareFilter($value)
				: ($value === "~" ? null : $value);
		}
		return $preparedFilter;
	}


	private function prepareSort(?array $order): array
	{
		$preparedSort = [];
		foreach ((array) $order as $key => $value) {
			if (is_numeric($key)) {
				if (is_array($value)) {
					$ex = $value[0];
					$preparedSort[] = [
						'expression' => is_array($ex)
							? array_map(fn($v) => $this->prepareColumn($v), $ex)
							: $this->prepareColumn($ex),
						'direction' => $value[1],
					];
					continue;
				}
				$preparedSort[] = [
					'expression' => $this->prepareColumn($value),
					'direction' => ICollection::ASC
				];
			} else {
				$preparedSort[] = [
					'expression' => $this->prepareColumn($key),
					'direction' => strtoupper($value),
				];
			}
		}
		return $preparedSort;
	}


	private function prepareColumn(string $column): string
	{
		if (str_contains($column, '.')) {
			return str_replace('.', '->', $column);
		}
		return $column;
	}
}
