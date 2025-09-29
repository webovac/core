<?php

declare(strict_types=1);

namespace Webovac\Core\Lib;

use Nette\Caching\Cache;
use Nette\Caching\Storage;
use Stepapo\Utils\Service;


class CmsCache implements Service
{
	private Cache $cache;


	public function __construct(
		private Storage $storage,
		private ModeChecker $modeChecker,
	) {
		$this->cache = new Cache($this->storage);
	}


	public function clean(?array $conditions = null): void
	{
		if (!$this->modeChecker->isTest()) {
			$this->cache->clean($conditions);
		}
	}
}