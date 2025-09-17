<?php

namespace Webovac\Api\Lib;

use Nette\Utils\Json;
use Stepapo\Utils\Config;


class Query extends Config
{
	public const DEFAULT_LIMIT = 50;

	public ?array $select = null;
	public ?array $where = null;
	public ?array $order = null;
	public ?int $offset = null;
	public ?int $limit = null;
	public int $page = 1;


	public static function createFromArray(mixed $config = [], mixed $key = null, bool $skipDefaults = false, mixed $parentKey = null): static
	{
		if (isset($config['query'])) {
			$config = is_string($config['query'])
				? Json::decode($config['query'], forceArrays: true)
				: $config['query'];
		}
		if (isset($config['select']) && is_string($config['select'])) {
			$config['select'] = Json::decode($config['select'], forceArrays: true);
		}
		if (isset($config['where']) && is_string($config['where'])) {
			$config['where'] = Json::decode($config['where'], forceArrays: true);
		}
		if (isset($config['order']) && is_string($config['order'])) {
			$config['order'] = Json::decode($config['order'], forceArrays: true);
		}
		if (!isset($config['offset'])) {
			if (!isset($config['page']) || $config['page'] <= 0) {
				$config['page'] = 1;
			} else {
				$config['page'] = (int)	$config['page'];
			}
		}
		$config['limit'] = (int) (isset($config['limit']) ? min($config['limit'], static::DEFAULT_LIMIT) : static::DEFAULT_LIMIT);
		$config['offset'] ??= ($config['page'] - 1) * $config['limit'];
		return parent::createFromArray($config);
	}
}
