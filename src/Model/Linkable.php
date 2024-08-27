<?php

declare(strict_types=1);

namespace Webovac\Core\Model;


interface Linkable
{
	function getParameters(): array;
	function getPageName(): string;
	function getEntityIcon(): string;
}
