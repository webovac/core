<?php

namespace Webovac\Core;

class InstallGroup
{
	public function __construct(
		public string $name,
		public string $title,
		public array $dependencies = [],
		public ?int $iteration = null,
	) {}


	public function isDependentOn(InstallGroup $group): bool
	{
		return in_array($group->name, $this->dependencies, true);
	}
}