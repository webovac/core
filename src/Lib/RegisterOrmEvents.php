<?php

declare(strict_types=1);

namespace Webovac\Core\Lib;

use Stepapo\Utils\Service;
use Webovac\Core\HasOrmEvents;


class RegisterOrmEvents implements Service
{
	/** @param HasOrmEvents[] $withOrmEvents */
	public function __construct(
		private array $withOrmEvents
	) {}


	public function register(): void
	{
		foreach ($this->withOrmEvents as $hasOrmEvents) {
			$hasOrmEvents->registerOrmEvents();
		}
	}
}