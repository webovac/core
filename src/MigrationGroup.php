<?php

declare(strict_types=1);

namespace Webovac\Core;


class MigrationGroup
{
	public function __construct(
		public string $name,
		public string $dir,
		public array $dependencies = [],
	) {}
}