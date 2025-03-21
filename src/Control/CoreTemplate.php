<?php

declare(strict_types=1);

namespace Webovac\Core\Control;

use Nette\Application\UI\Control;
use Nette\Application\UI\Presenter;
use Nette\InvalidArgumentException;
use Nette\Security\User;
use ReflectionClass;
use ReflectionException;


trait CoreTemplate
{
	public Presenter $presenter;
	public Control $control;
	public User $user;
	public string $baseUrl;
	public string $basePath;
	public array $flashes = [];


	/**
	 * @throws ReflectionException
	 */
	public function renderFile(string $moduleClass, string $componentClass, string $templateName, array $params = []): void
	{
		$moduleRf = new ReflectionClass($moduleClass);
		$dir = dirname($moduleRf->getFileName());
		$componentRf = new ReflectionClass($componentClass);
		$componentName = str_replace('Control', '', $componentRf->getShortName());
		if (!file_exists($path = "$dir/Control/$componentName/$templateName.latte")) {
			throw new InvalidArgumentException("Template '$path' does not exist.");
		}
		$this->render($path, $params);
	}
}