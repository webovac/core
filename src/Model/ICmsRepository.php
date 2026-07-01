<?php

declare(strict_types=1);

namespace Webovac\Core\Model;

use Build\Model\Web\WebData;
use Stepapo\Model\Orm\IStepapoRepository;


/**
 * @method CmsEntity|null getBy(array $conds)
 * @method CmsEntity create()
 */
interface ICmsRepository extends IStepapoRepository
{
	function getByParameters(?array $parameters = null, ?string $path = null, ?WebData $webData = null): ?CmsEntity;
	function getEntityListByPath(string $path, ?WebData $webData = null): array;
	function isForbiddenRepository(WebData $webData): bool;
	function getKeyParameter(): string;
	function prefix(string $prefix, array $filter): ?array;
}
