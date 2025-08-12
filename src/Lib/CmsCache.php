<?php

namespace Webovac\Core\Lib;

use Nette\Caching\Cache;
use Stepapo\Utils\Service;


class CmsCache implements Service
{
	public function __construct(
		private bool $testMode,
		private Cache $cache,
	) {}


	public function clean(?array $conditions = null): void
	{
		if (!$this->testMode) {
			$this->cache->clean($conditions);
		}
	}


	public function remove(mixed $key): void
	{
		if (!$this->testMode) {
			$this->cache->remove($key);
		}
	}
}