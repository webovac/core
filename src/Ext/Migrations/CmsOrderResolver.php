<?php

declare(strict_types=1);

namespace Webovac\Core\Ext\Migrations;

use Nextras\Migrations\Engine\OrderResolver;
use Nextras\Migrations\Entities\File;
use Nextras\Migrations\Entities\Group;
use Nextras\Migrations\LogicException;
use Tracy\Dumper;


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
			if ($a->group !== $b->group) {
				$cmpA = $a->group->isDependentOn($b->group);
				$cmpB = $b->group->isDependentOn($a->group);
				if ($cmpA xor $cmpB) {
					return $cmpA ? 1 : -1;
				} elseif ($cmpA && $cmpB) {
					$names = [
						"$a->name",
						"$b->name",
					];
					sort($names);
					throw new LogicException(sprintf(
						'Unable to determine order for migrations "%s" and "%s".',
						$names[0], $names[1]
					));
				}
			}
			return strcmp($a->name, $b->name);
		});

		return $files;
	}
}