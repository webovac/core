<?php

declare(strict_types=1);

namespace Webovac\Core\DI;

use App\Lib\TemplateFactory;
use App\Model\DataModel;
use App\Model\Orm;
use Contributte\FormMultiplier\DI\MultiplierExtension;
use Nette\DI\Extensions\DecoratorExtension;
use Nette\DI\Extensions\SearchExtension;
use Nette\PhpGenerator\ClassType;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Nextras\Dbal\Bridges\NetteDI\DbalExtension;
use Nextras\Migrations\Bridges\NetteDI\MigrationsExtension;
use Nextras\Migrations\Extensions\SqlHandler;
use Nextras\Orm\Bridges\NetteDI\OrmExtension;
use Webovac\Core\Command\Command;
use Webovac\Core\Command\InstallCommand;
use Webovac\Core\Command\InstallNewCommand;
use Webovac\Core\Command\MigrateCommand;
use Webovac\Core\Definition\MysqlDefinitionProcessor;
use Webovac\Core\Definition\PgsqlDefinitionProcessor;
use Webovac\Core\Ext\Orm\CmsPhpDocRepositoryFinder;
use Webovac\Core\Factory;
use Webovac\Core\Lib\NeonHandler;
use Webovac\Core\Model\CmsDataRepository;
use Webovac\Core\Model\CmsRepository;
use Webovac\Core\Module;
use Webovac\Core\Definition\PqsqlStructureGenerator;


class CoreExtension extends BaseExtension
{
	private OrmExtension $ormExtension;
	private MultiplierExtension $multiplierExtension;
	private DbalExtension $dbalExtension;
	private MigrationsExtension $migrationsExtension;
	private DecoratorExtension $decoratorExtension;
	private SearchExtension $projectSearchExtension;


	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'host' => Expect::string()->required(),
			'db' => Expect::structure([
				'driver' => Expect::string()->required(),
				'database' => Expect::string()->required(),
				'username' => Expect::string()->required(),
				'password' => Expect::string(),
			]),
		]);
	}


	public function loadConfiguration(): void
	{
		parent::loadConfiguration();
		$builder = $this->getContainerBuilder();
		$builder->addDefinition($this->prefix('neonHandler'))
			->setFactory(NeonHandler::class, [['host' => $this->config->host], $builder->parameters['debugMode']]);
		$definitionProcessor = $builder->addDefinition($this->prefix('definitionProcessor'))
			->setFactory($this->config->db->driver === 'pgsql' ? PgsqlDefinitionProcessor::class : MysqlDefinitionProcessor::class);
		if ($this->config->db->driver === 'mysql') {
			$definitionProcessor->addSetup('setDefaultSchema', [$this->config->db->database]);
		}
		$this->createOrmExtension();
		$this->createMultiplierExtension();
		$this->createDecoratorExtension();
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


	protected function createDecoratorExtension(): void
	{
		$this->decoratorExtension = new DecoratorExtension;
		$this->decoratorExtension->setCompiler($this->compiler, 'decorator');
		$config = $this->processSchema($this->decoratorExtension->getConfigSchema(), $this->getDecoratorConfig());
		$this->decoratorExtension->setConfig($config);
		$this->decoratorExtension->loadConfiguration();
	}


	private function createOrmExtension(): void
	{
		$this->ormExtension = new OrmExtension;
		$this->ormExtension->setCompiler($this->compiler, $this->prefix('orm'));
		$config = $this->processSchema($this->ormExtension->getConfigSchema(), [
			'model' => Orm::class,
			'repositoryFinder' => CmsPhpDocRepositoryFinder::class,
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
		$this->projectSearchExtension->beforeCompile();
		$this->decoratorExtension->beforeCompile();
		$this->ormExtension->beforeCompile();
		$this->multiplierExtension->beforeCompile();
		$this->dbalExtension->beforeCompile();
		$this->migrationsExtension->beforeCompile();
	}


	public function afterCompile(ClassType $class): void
	{
		parent::afterCompile($class);
		$this->projectSearchExtension->afterCompile($class);
		$this->decoratorExtension->afterCompile($class);
		$this->ormExtension->afterCompile($class);
		$this->multiplierExtension->afterCompile($class);
		$this->dbalExtension->afterCompile($class);
		$this->migrationsExtension->afterCompile($class);
	}


	protected function getProjectSearchConfig(): array
	{
		$rootDir = $this->getContainerBuilder()->parameters['rootDir'];
		$appDir = "$rootDir/app";
		return [
			'module' => ['in' => $appDir, 'implements' => Module::class],
			'command' => ['in' => $appDir, 'implements' => Command::class],
			'control' => ['in' => $appDir, 'extends' => Factory::class],
			'lib' => ['in' => $appDir, 'classes' => 'App\**\Lib\**'],
			'dataRepository' => ['in' => $appDir, 'extends' => CmsDataRepository::class],
		];
	}


	private function getDecoratorConfig(): array
	{
		return [
			TemplateFactory::class => ['inject' => true],
			DataModel::class => ['inject' => true],
			CmsDataRepository::class => ['inject' => true],
			CmsRepository::class => ['inject' => true],
		];
	}
}