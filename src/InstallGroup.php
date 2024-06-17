<?php

declare(strict_types=1);

namespace Webovac\Core;


class InstallGroup
{
	public function __construct(
		public string $name,
		public string $title,
		public array $dependencies = [],
	) {}


	public function isDependentOn(InstallGroup $group): bool
	{
		return in_array($group->name, $this->dependencies, true);
	}
}