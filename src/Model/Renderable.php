<?php

namespace Webovac\Core\Model;

use Webovac\Core\Control\BaseControl;

interface Renderable
{
	public function getComponent(string $moduleClass, string $templateName): BaseControl;
	public function getPageName(): string;
	public function getEntityIcon(): string;
	public function getParameters(): array;
}
