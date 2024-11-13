<?php

declare(strict_types=1);

namespace Webovac\Core\Model;

use Webovac\Core\Control\BaseControl;


trait RenderableTrait
{
	public function getComponent(string $moduleClass, string $templateName): BaseControl
	{
		return $this->component->create($this, $moduleClass, $templateName);
	}


	public function render(string $moduleClass, string $template): void
	{
		$this->getComponent($moduleClass, $template)->render();
	}
}