<?php

declare(strict_types=1);

namespace Webovac\Core\Ext\Migrations;

use DateTime;
use Nette\Utils\FileInfo;
use Nextras\Migrations\Engine\OrderResolver;
use Nextras\Migrations\Engine\Runner;
use Nextras\Migrations\Entities\File;
use Nextras\Migrations\Entities\Group;
use Nextras\Migrations\Entities\Migration;
use Nextras\Migrations\Exception;
use Nextras\Migrations\ExecutionException;
use Nextras\Migrations\IConfiguration;
use Nextras\Migrations\IDriver;
use Nextras\Migrations\IExtensionHandler;
use Nextras\Migrations\IPrinter;
use Nextras\Migrations\LockException;
use Nextras\Migrations\LogicException;


class CmsRunner extends Runner
{
	private OrderResolver $orderResolver;
	/** @var list<Group> */ private array $groups = [];
	/** @var array<string, IExtensionHandler> (extension => IExtensionHandler) */ private array $extensionsHandlers = [];


	public function __construct(
		private IDriver $driver,
		private IPrinter $printer,
	) {
		parent::__construct($driver, $printer);
		$this->orderResolver = new OrderResolver;
	}


	public function addGroup(Group $group): self
	{
		$this->groups[] = $group;
		return $this;
	}


	public function addExtensionHandler(string $extension, IExtensionHandler $handler): self
	{
		if (isset($this->extensionsHandlers[$extension])) {
			throw new LogicException("Extension '$extension' has already been defined.");
		}

		$this->extensionsHandlers[$extension] = $handler;
		return $this;
	}


	/**
	 * @param  self::MODE_*   $mode
	 */ 
	public function run(string $mode = self::MODE_CONTINUE, ?IConfiguration $config = null): void
	{
		if ($config) {
			foreach ($config->getGroups() as $group) {
				$this->addGroup($group);
			}

			foreach ($config->getExtensionHandlers() as $ext => $handler) {
				$this->addExtensionHandler($ext, $handler);
			}
		}

		if ($mode === self::MODE_INIT) {
			$this->driver->setupConnection();
			$this->printer->printSource($this->driver->getInitTableSource() . "\n");
			$files = $this->getFiles();
			$files = $this->orderResolver->resolve([], $this->groups, $files, self::MODE_RESET);
			$this->printer->printSource($this->driver->getInitMigrationsSource($files));
			return;
		}

		try {

			$this->driver->setupConnection();
			$this->driver->lock();

			$this->printer->printIntro($mode);
//			if ($mode === self::MODE_RESET) {
//				$this->driver->emptyDatabase();
//			}

			$this->driver->createTable();
			$migrations = $this->driver->getAllMigrations();
			$files = $this->getFiles();
			$toExecute = $this->orderResolver->resolve($migrations, $this->groups, $files, $mode);
			$this->printer->printToExecute($toExecute);

			foreach ($toExecute as $file) {
				$time = microtime(true);
				$queriesCount = $this->execute($file);
				$this->printer->printExecute($file, $queriesCount, microtime(true) - $time);
			}

			$this->driver->unlock();
			$this->printer->printDone();

		} catch (LockException $e) {
			$this->printer->printError($e);

		} catch (Exception $e) {
			$this->driver->unlock();
			$this->printer->printError($e);
		}
	}


	public function getExtension(string $name): IExtensionHandler
	{
		if (!isset($this->extensionsHandlers[$name])) {
			throw new LogicException("Extension '$name' not found.");
		}

		return $this->extensionsHandlers[$name];
	}


	/**
	 * @return int  number of executed queries
	 */
	protected function execute(File $file): int
	{
		$this->driver->beginTransaction();

		$migration = new Migration;
		$migration->group = $file->group->name;
		$migration->filename = $file->name;
		$migration->checksum = $file->checksum;
		$migration->executedAt = new DateTime('now');

		$this->driver->insertMigration($migration);

		try {
			$queriesCount = $this->getExtension($file->extension)->execute($file);

		} catch (\Exception $e) {
			$this->driver->rollbackTransaction();
			throw new ExecutionException(sprintf('Executing migration "%s" has failed.', $file->path), 0, $e);
		}

		$this->driver->markMigrationAsReady($migration);
		$this->driver->commitTransaction();

		return $queriesCount;
	}


	private function getFiles(): array
	{
		$files = [];
		foreach ($this->groups as $group) {
			/** @var FileInfo $f */
			foreach ($group->files as $f) {
				$file = new File;
				$file->group = $group;
				$file->name = $f->getFilename();
				$file->path = $f->getPathname();
				$file->extension = $f->getExtension();
				$file->checksum = md5(str_replace(["\r\n", "\r"], "\n", @file_get_contents($f->getPathname())));
				$files[] = $file;
			}
		}
		return $files;
	}
}