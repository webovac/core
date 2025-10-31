<?php

declare(strict_types=1);

namespace Webovac\Core\DI;

use Build\Model\Orm;
use Contributte\FormMultiplier\DI\MultiplierExtension;
use Nette\DI\Extensions\DecoratorExtension;
use Nette\DI\Extensions\SearchExtension;
use Nette\PhpGenerator\ClassType;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Nextras\Dbal\Bridges\NetteDI\DbalExtension;
use Nextras\Migrations\Bridges\NetteDI\MigrationsExtension;
use Nextras\Orm\Bridges\NetteDI\OrmExtension;
use Stepapo\Model\DI\ModelExtension;
use Stepapo\Utils\DI\StepapoExtension;
use Stepapo\Utils\Injectable;
use Stepapo\Utils\Service;
use Webovac\Core\Ext\Orm\CmsPhpDocRepositoryFinder;
use Webovac\Core\Lib\KeyProvider;
use Webovac\Core\Lib\ModeChecker;


class CoreExtension extends StepapoExtension
{
	private ModelExtension $modelExtension;
	private OrmExtension $ormExtension;
	private MultiplierExtension $multiplierExtension;
	private DbalExtension $dbalExtension;
	private MigrationsExtension $migrationsExtension;
	private SearchExtension $projectSearchExtension;


	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'parameters' => Expect::array([
				'host' => Expect::string()->required(),
			])->otherItems(),
			'db' => Expect::structure([
				'driver' => Expect::string()->required(),
				'database' => Expect::string()->required(),
				'username' => Expect::string()->required(),
				'password' => Expect::string(),
				'schemas' => Expect::arrayOf('string'),
			]),
			'testMode' => Expect::bool()->default(false),
		]);
	}


	public function loadConfiguration(): void
	{
		parent::loadConfiguration();
		$builder = $this->getContainerBuilder();
		$builder->addDefinition($this->prefix('keyProvider'))
			->setFactory(KeyProvider::class, [$this->config->parameters]);
		$builder->addDefinition($this->prefix('modeChecker'))
			->setFactory(ModeChecker::class, [$builder->parameters['debugMode'], $this->config->testMode]);
		$this->createModelExtension();
		$this->createOrmExtension();
		$this->createMultiplierExtension();
		$this->createProjectSearchExtension();
		$this->createDbalExtension();
		$this->createMigrationsExtension();
	}


	protected function createProjectSearchExtension(): void
	{
		$rootDir = $this->getContainerBuilder()->parameters['rootDir'];
		$this->projectSearchExtension = new SearchExtension("$rootDir/temp/cache/nette.search");
		$this->projectSearchExtension->setCompiler($this->compiler, 'nette.search');
		$config = $this->processSchema($this->projectSearchExtension->getConfigSchema(), $this->getProjectSearchConfig());
		$this->projectSearchExtension->setConfig($config);
		$this->projectSearchExtension->loadConfiguration();
	}


	protected function createModelExtension(): void
	{
		$this->modelExtension = new ModelExtension;
		$this->modelExtension->setCompiler($this->compiler, $this->prefix('model'));
		$config = $this->processSchema($this->modelExtension->getConfigSchema(), [
			'parameters' => $this->config->parameters,
			'testMode' => $this->config->testMode,
			'driver' => $this->config->db->driver,
			'database' => $this->config->db->database,
			'schemas' => $this->config->db->schemas ?: ($this->config->db->driver === 'pgsql' ? ['public'] : [$this->config->db->database]),
		]);
		$this->modelExtension->setConfig($config);
		$this->modelExtension->loadConfiguration();
	}


	private function createOrmExtension(): void
	{
		$this->ormExtension = new OrmExtension;
		$this->ormExtension->setCompiler($this->compiler, $this->prefix('orm'));
		$config = $this->processSchema($this->ormExtension->getConfigSchema(), [
			'model' => Orm::class,
		]);
		$this->ormExtension->setConfig($config);
		$this->ormExtension->loadConfiguration();
	}


	private function createMultiplierExtension(): void
	{
		$this->multiplierExtension = new MultiplierExtension;
		$this->multiplierExtension->setCompiler($this->compiler, $this->prefix('multiplier'));
		$config = $this->processSchema($this->multiplierExtension->getConfigSchema(), [
			'name' => 'addMultiplier',
		]);
		$this->multiplierExtension->setConfig($config);
		$this->multiplierExtension->loadConfiguration();
	}


	private function createDbalExtension(): void
	{
		$this->dbalExtension = new DbalExtension;
		$this->dbalExtension->setCompiler($this->compiler, $this->prefix('dbal'));
		$config = $this->processSchema($this->dbalExtension->getConfigSchema(), [
			'driver' => $this->config->db->driver === 'mysql' ? 'mysqli' : $this->config->db->driver,
			'host' => 'localhost',
			'database' => $this->config->db->database,
			'username' => $this->config->db->username,
			'password' => $this->config->db->password,
			'connectionTz' => 'auto-offset',
			'searchPath' => ['public'],
			'panelQueryExplain' => false,
		]);
		$this->dbalExtension->setConfig($config);
		$this->dbalExtension->loadConfiguration();
	}


	private function createMigrationsExtension(): void
	{
		$rootDir = $this->getContainerBuilder()->parameters['rootDir'];
		$this->migrationsExtension = new MigrationsExtension;
		$this->migrationsExtension->setCompiler($this->compiler, $this->prefix('migrations'));
		$config = $this->processSchema($this->migrationsExtension->getConfigSchema(), [
			'dir' => "$rootDir/migrations",
			'driver' => $this->config->db->driver,
			'dbal' => 'nextras',
			'withDummyData' => $this->getContainerBuilder()->parameters['debugMode'],
		]);
		$this->migrationsExtension->setConfig($config);
		$this->migrationsExtension->loadConfiguration();
	}


	public function beforeCompile(): void
	{
		parent::beforeCompile();
		$this->modelExtension->beforeCompile();
		$this->projectSearchExtension->beforeCompile();
		$this->ormExtension->beforeCompile();
		$this->multiplierExtension->beforeCompile();
		$this->dbalExtension->beforeCompile();
		$this->migrationsExtension->beforeCompile();
	}


	public function afterCompile(ClassType $class): void
	{
		parent::afterCompile($class);
		$this->modelExtension->afterCompile($class);
		$this->projectSearchExtension->afterCompile($class);
		$this->ormExtension->afterCompile($class);
		$this->multiplierExtension->afterCompile($class);
		$this->dbalExtension->afterCompile($class);
		$this->migrationsExtension->afterCompile($class);
	}


	protected function getProjectSearchConfig(): array
	{
		$rootDir = $this->getContainerBuilder()->parameters['rootDir'];
		$appDir = "$rootDir/app";
		$buildDir = "$rootDir/build";
		return [
			['in' => $appDir, 'implements' => Service::class],
			['in' => $buildDir, 'implements' => Service::class],
		];
	}
}