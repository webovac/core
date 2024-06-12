<?php

declare(strict_types=1);

namespace Webovac\Core\Ext\Migrations;

use Nextras\Migrations\Engine\OrderResolver;
use Nextras\Migrations\Entities\File;
use Nextras\Migrations\Entities\Group;


class CmsOrderResolver extends OrderResolver
{
	/**
	 * @param  list<File> $files
	 * @param  array<string, Group>  $groups (name => Group)
	 * @return list<File> sorted
	 */
	protected function sortFiles(array $files, array $groups): array
	{
		usort($files, function (File $a, File $b) use ($groups): int {
			$cmp = strcmp($a->name, $b->name);
			if ($cmp === 0 && $a !== $b) {
				$cmpA = $this->isGroupDependentOn($groups, $a->group, $b->group);
				$cmp = ($cmpA ? -1 : 1);
			}
			return $cmp;
		});

		return $files;
	}
}