<?php

declare(strict_types=1);

namespace Webovac\Core\Ext\Migrations;

use Nette\Utils\FileInfo;
use Nextras\Migrations\Entities\Group;
use Nextras\Migrations\IDiffGenerator;
use Webovac\Core\MigrationGroup;


class CmsGroup extends Group
{
	/** @var string */
	public $name;

	/** @var bool */
	public $enabled;

	/** @var FileInfo[] */
	public array $files;

	/** @var list<string> */
	public $dependencies;

	/** @var IDiffGenerator|null */
	public $generator;

	public MigrationGroup $migrationGroup;


	public function isDependentOn(Group $group): bool
	{
		return in_array($group->name, $this->dependencies, true);
	}
}