<?php

declare(strict_types=1);

namespace Webovac\Core\Command;

use Build\Model\DataModel;
use Stepapo\Utils\Command\Command;
use Stepapo\Utils\Printer;


class RebuildCacheCommand implements Command
{
	private Printer $printer;


	public function __construct(
		private DataModel $dataModel,
	) {
		$this->printer = new Printer;
	}


	public function run(): int
	{
		$start = microtime(true);
		$this->printer->printBigSeparator();
		$this->printer->printLine('Cache', 'aqua');
		$this->printer->printSeparator();
		$this->dataModel->refreshCache();
		$end = microtime(true);
		$this->printer->printLine(sprintf("%0.3f s | OK", $end - $start), 'lime');
		return 0;
	}
}