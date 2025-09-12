<?php

namespace Webovac\Core\Lib;

use Nette\Caching\Cache;
use Stepapo\Utils\Service;


class CmsCache implements Service
{
	public function __construct(
		private Cache $cache,
		private ModeChecker $modeChecker,
	) {}


	public function clean(?array $conditions = null): void
	{
		if (!$this->modeChecker->isTest()) {
			$this->cache->clean($conditions);
		}
	}


	public function remove(mixed $key): void
	{
		if (!$this->modeChecker->isTest()) {
			$this->cache->remove($key);
		}
	}
}