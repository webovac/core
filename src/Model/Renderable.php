<?php

declare(strict_types=1);

namespace Webovac\Core\Model;

use Webovac\Core\Control\BaseControl;


interface Renderable
{
	function getComponent(string $moduleClass, string $templateName): BaseControl;
	function render(string $moduleClass, string $templateName): void;
	function getEntityIcon(): ?string;
}
