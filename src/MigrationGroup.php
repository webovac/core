<?php

declare(strict_types=1);

namespace Webovac\Core;


abstract class MigrationGroup
{
	public function __construct(
		public string $name,
		public string $class,
		public array $dependencies = [],
	) {}
}