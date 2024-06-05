<?php

declare(strict_types=1);

namespace Webovac\Core\Command;

use App\Model\DataModel;
use App\Model\Orm;
use LogicException;
use Nette\Neon\Neon;
use Nette\Utils\FileInfo;
use Nette\Utils\Finder;
use ReflectionClass;
use Stepapo\Dataset\Utils;
use Webovac\Core\InstallGroup;
use Webovac\Core\Lib\CmsPrinter;
use Webovac\Core\Model\CmsDataRepository;
use Webovac\Core\Module;


class InstallCommand implements Command
{
	private array $paths = [];
	/** @var InstallGroup[] */ private array $groups = [];


	/** @param Module[] $modules */
	public function __construct(
		private array $params,
		private CmsPrinter $printer,
		private DataModel $dataModel,
		private Orm $orm,
		array $modules,
	) {
		foreach ($modules as $module) {
			$reflection = new ReflectionClass($module);
			if (file_exists($installPath = dirname($reflection->getFileName()) . '/install')) {
				$this->paths[CmsDataRepository::MODE_INSTALL][] = $installPath;
			}
			if (file_exists($updatePath = dirname($reflection->getFileName()) . '/update')) {
				$this->paths[CmsDataRepository::MODE_UPDATE][] = $updatePath;
			}
			if (!method_exists($module, 'getInstallGroups')) {
				continue;
			}
			$this->groups = array_merge($this->groups, $module->getInstallGroups());
		}
		$this->sortGroups();
	}


	public function run(): int
	{
		foreach ($this->groups as $group) {
			$this->install($group);
		}
		foreach ($this->groups as $group) {
			$this->install($group, 'Updating', CmsDataRepository::MODE_UPDATE);
		}
		$this->orm->flush();
		return 0;
	}


	private function install(InstallGroup $group, string $print = 'Installing', string $mode = CmsDataRepository::MODE_INSTALL): void
	{
		$files = Finder::findFiles("$group->name.*.neon")->from($this->paths[$mode] ?? [])->sortBy(
			fn(FileInfo $a, FileInfo $b) => $a->getFilename() <=> $b->getFilename()
		);
		if (!$files->collect()) {
			return;
		}
		if ($group->iteration < 2) {
			$this->printer->printSeparator();
			$this->printer->printLine("$print $group->title");
		}
		if ($group->iteration) {
			$this->printer->printLine("$group->iteration:");
		}
		foreach ($files as $file) {
			$text = str_replace(["$group->name.", ".neon"], "", $file->getFilename());
			$config = (array) Neon::decode($file->read());
			$this->printer->printText("- " . $text);
			$parsedConfig = Utils::replaceParams($config, $this->params);
			$this->dataModel->{$group->name . 'Repository'}->createFromConfig($parsedConfig, $mode, $group->iteration);
			$this->printer->printDone();
		}
		$this->printer->printOk();
	}


	protected function sortGroups(): void
	{
		usort($this->groups, function (InstallGroup $a, InstallGroup $b): int {
			$cmpA = $a->isDependentOn($b);
			$cmpB = $b->isDependentOn($a);
			if ($cmpA xor $cmpB) {
				$cmp = $cmpA ? 1 : -1;
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
			} else {
				$cmp = strcmp($a->name, $b->name);
				if ($cmp === 0 && $a !== $b) {
					$cmp = $a->iteration <=> $b->iteration;
				}
			}
			return $cmp;
		});
	}
}