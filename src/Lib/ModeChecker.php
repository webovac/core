<?php

namespace Webovac\Core\Lib;

use Stepapo\Utils\Service;


class ModeChecker implements Service
{
	public function __construct(
		private bool $debugMode = false,
		private bool $testMode = false,
	) {}


	public function isDebug(): bool
	{
		return $this->debugMode;
	}


	public function isTest(): bool
	{
		return $this->testMode;
	}


	public function isProd(): bool
	{
		return !$this->isDebug() && !$this->isTest();
	}
}