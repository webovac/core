<?php

declare(strict_types=1);

namespace Webovac\Core\Lib;

use Stepapo\Utils\Service;


class KeyProvider implements Service
{
	public function __construct(
		private array $keys,
	) {}


	public function getKey(string $keyName): ?string
	{
		return $this->keys[$keyName] ?? null;
	}


	public function replaceKey(string $string): string
	{
		return preg_replace_callback('/{(.+?)}/', fn(array $m) => $this->getKey($m[1]), $string);
	}
}
