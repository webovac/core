<?php

declare(strict_types=1);

namespace Webovac\Core\Command;

use Stepapo\Utils\Command\Command;
use Stepapo\Utils\Printer;
use Webovac\Core\Lib\CacheRefresher;


class RebuildCacheCommand implements Command
{
	private Printer $printer;


	public function __construct(
		private CacheRefresher $cacheRefresher,
	) {
		$this->printer = new Printer;
	}


	public function run(): int
	{
		$start = microtime(true);
		$this->printer->printBigSeparator();
		$this->printer->printLine('Cache', 'aqua');
		$this->printer->printSeparator();
		$this->cacheRefresher->refreshCache();
		$end = microtime(true);
		$this->printer->printLine(sprintf("%0.3f s | OK", $end - $start), 'lime');
		return 0;
	}
}
