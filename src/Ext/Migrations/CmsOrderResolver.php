<?php

declare(strict_types=1);

namespace Webovac\Core\Ext\Migrations;

use Nextras\Migrations\Engine\OrderResolver;
use Nextras\Migrations\Entities\File;
use Nextras\Migrations\LogicException;
use Webovac\Core\DefinitionGroup;


class CmsOrderResolver extends OrderResolver
{
	/**
	 * @param  list<File> $files
	 * @param  array<string, CmsGroup>  $groups (name => CmsGroup)
	 * @return list<File> sorted
	 */
	protected function sortFiles(array $files, array $groups): array
	{
		usort($files, function (File $a, File $b) use ($groups): int {
			if ($a->group !== $b->group) {
				$aIsDefinition = $a->group->migrationGroup instanceof DefinitionGroup;
				$bIsDefinition = $b->group->migrationGroup instanceof DefinitionGroup;
				if ($aIsDefinition xor $bIsDefinition) {
					return $bIsDefinition ? 1 : -1;
				}
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
				return strcmp($a->group->name, $b->group->name);
			}
			$aIsInsert = str_contains($a->name, 'insert');
			$bIsInsert = str_contains($b->name, 'insert');
			if ($aIsInsert xor $bIsInsert) {
				return $bIsInsert ? 1 : -1;
			}
			return strcmp($a->name, $b->name);
		});

		return $files;
	}
}