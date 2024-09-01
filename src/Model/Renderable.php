<?php

declare(strict_types=1);

namespace Webovac\Core\Model;

use Webovac\Core\Control\BaseControl;


interface Renderable
{
	function getComponent(string $moduleClass, string $templateName): BaseControl;
	function getEntityIcon(): ?string;
}
