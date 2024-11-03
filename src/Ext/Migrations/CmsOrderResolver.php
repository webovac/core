<?php

declare(strict_types=1);

namespace Webovac\Core\Ext\Migrations;

use Nextras\Migrations\Engine\OrderResolver;
use Nextras\Migrations\Entities\File;
use Nextras\Migrations\LogicException;
use Tracy\Dumper;
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
		uasort($files, fn(File $a, File $b): int =>
			[$a->group->name, str_contains($b->name, 'insert'), $a->name]
			<=>
			[$b->group->name, str_contains($a->name, 'insert'), $b->name]
		);
		$sortedFiles = [];
		$doneFiles = [];
		$doneGroups = [];
		while(count($files) > count($sortedFiles)) {
			$resolvable = false;
			foreach ($files as $key => $file) {
				if (isset($doneFiles[$key])) {
					continue;
				}
				$resolved = true;
				if ($file->group->dependencies) {
					foreach ($file->group->dependencies as $dependency) {
						if (!isset($doneGroups[$dependency])) {
							$resolved = false;
							break;
						}
					}
				}
				if ($resolved) {
					$doneFiles[$key] = true;
					$doneGroups[$file->group->name] = true;
					$sortedFiles[$key] = $file;
					$resolvable = true;
				}
			}
			if (!$resolvable) {
				throw new LogicException("Order of file \"$file->name\" in group \"{$file->group->name}\" could not be resolved.");
			}
		}
		$files = $sortedFiles;
		uasort($files, fn(File $a, File $b): int => $b->group->migrationGroup instanceof DefinitionGroup <=> $a->group->migrationGroup instanceof DefinitionGroup);
		return $files;
	}
}