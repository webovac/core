<?php

namespace Webovac\Core\DI;

use App\Model\Orm;
use Contributte\FormMultiplier\DI\MultiplierExtension;
use Nette\DI\ContainerBuilder;
use Nette\PhpGenerator\ClassType;
use Nextras\Migrations\Bridges\NetteDI\MigrationsExtension;
use Nextras\Orm\Bridges\NetteDI\OrmExtension;
use Webovac\Core\Command\Command;
use Webovac\Core\Core;
use Webovac\Core\Ext\Orm\CmsPhpDocRepositoryFinder;
use Webovac\Core\Lib\Dir;
use Webovac\Core\Lib\InstallCommand;
use Webovac\Core\Module;


class CoreExtension extends BaseExtension
{
	private OrmExtension $ormExtension;
	private MigrationsExtension $migrationsExtension;
	private MultiplierExtension $multiplierExtension;


	public function __construct(
		private string $appDir,
		private array $params,
	) {}


	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$builder->addDefinition($this->prefix('dir'))
			->setFactory(Dir::class, [$this->appDir]);
		$builder->addDefinition($this->prefix('installer'))
			->setFactory(InstallCommand::class, [$this->params]);
		$this->createOrmExtension($builder);
		$this->createMultiplierExtension($builder);
		$this->createSearchExtension($builder);
		$this->compiler->loadDefinitionsFromConfig(
			(array) $this->loadFromFile(__DIR__ . '/config.neon')['services'],
		);
	}


	private function createOrmExtension(ContainerBuilder $builder): void
	{
		$this->ormExtension = new OrmExtension();
		$this->ormExtension->setCompiler($this->compiler, $this->prefix('orm'));
		$config = $this->processSchema($this->ormExtension->getConfigSchema(), [
			'model' => Orm::class,
			'repositoryFinder' => CmsPhpDocRepositoryFinder::class,
		]);
		$this->ormExtension->setConfig($config);
		$this->ormExtension->loadConfiguration();
	}


	private function createMultiplierExtension(ContainerBuilder $builder): void
	{
		$this->multiplierExtension = new MultiplierExtension();
		$this->multiplierExtension->setCompiler($this->compiler, $this->prefix('multiplier'));
		$config = $this->processSchema($this->multiplierExtension->getConfigSchema(), [
			'name' => 'addMultiplier',
		]);
		$this->multiplierExtension->setConfig($config);
		$this->multiplierExtension->loadConfiguration();
	}


	public function beforeCompile(): void
	{
		parent::beforeCompile();
		$this->ormExtension->beforeCompile();
		$this->multiplierExtension->beforeCompile();
	}


	public function afterCompile(ClassType $class): void
	{
		parent::afterCompile($class);
		$this->ormExtension->afterCompile($class);
		$this->multiplierExtension->afterCompile($class);
	}


	protected function getModuleName(): string
	{
		return Core::getModuleName();
	}


	protected function getSearchConfig(): array
	{
		return [
			'main' => [
				'in' => __DIR__ . '/../',
				'implements' => Module::class,
			],
			'command' => [
				'in' => __DIR__ . '/../',
				'implements' => Command::class,
			],
			'control' => [
				'in' => __DIR__ . '/../',
				'classes' => 'I*Control',
			],
			'lib' => [
				'in' => __DIR__ . '/../',
				'classes' => 'Webovac\Core\**\Lib\**\**',
			],
		];
	}
}