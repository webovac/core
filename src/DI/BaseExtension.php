<?php

declare(strict_types=1);

namespace Webovac\Core\DI;

use Nette\DI\CompilerExtension;
use Nette\DI\Extensions\SearchExtension;
use Nette\DI\InvalidConfigurationException;
use Nette\PhpGenerator\ClassType;
use Nette\Schema\Processor;
use Nette\Schema\Schema;
use Nette\Schema\ValidationException;


abstract class BaseExtension extends CompilerExtension
{
	private SearchExtension $searchExtension;


	protected function createSearchExtension(): void
	{
		$rootDir = $this->getContainerBuilder()->parameters['rootDir'];
		$this->searchExtension = new SearchExtension("$rootDir/temp/cache/{$this->name}.search");
		$this->searchExtension->setCompiler($this->compiler, $this->prefix('search'));
		$config = $this->processSchema($this->searchExtension->getConfigSchema(), $this->getSearchConfig());
		$this->searchExtension->setConfig($config);
		$this->searchExtension->loadConfiguration();
	}


	protected function processSchema(Schema $schema, array $config)
	{
		$processor = new Processor;
		try {
			return $processor->process($schema, $config);
		} catch (ValidationException $e) {
			throw new InvalidConfigurationException($e->getMessage());
		}
	}


	public function beforeCompile(): void
	{
		$this->searchExtension->beforeCompile();
	}


	public function afterCompile(ClassType $class): void
	{
		$this->searchExtension->afterCompile($class);
	}


	abstract protected function getModuleName(): string;


	abstract protected function getSearchConfig(): array;
}