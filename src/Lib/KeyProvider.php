<?php

namespace Webovac\Core\Lib;

use Stepapo\Utils\Service;

class KeyProvider implements Service
{
	public function __construct(
		private string $mapsKey,
	) {}


	public function getMapsKey(): string
	{
		return $this->mapsKey;
	}
}