<?php

declare(strict_types=1);

namespace Webovac\Core\Model;

use Webovac\Core\Control\BaseControl;


trait RenderableTrait
{
	public function getComponent(string $templateName = 'default', ?string $moduleClass = null): BaseControl
	{
		return $this->component->create($this, $moduleClass ?: $this->getModuleClass(), $templateName);
	}


	public function render(string $templateName = 'default', ?string $moduleClass = null): void
	{
		$this->getComponent($templateName, $moduleClass ?: $this->getModuleClass())->render();
	}


	public function getEntityIcon(): ?string
	{
		return null;
	}


	public function __toString(): string
	{
		return $this->getTitle();
	}


	abstract public function getModuleClass(): string;
}