<?php

declare(strict_types=1);

namespace Webovac\Core\Model;

use Nette\Application\UI\Component;


interface Linkable
{
	function getPageName(): string;
	function getParameters(): array;
	function getLink(Component $component): string;
}
