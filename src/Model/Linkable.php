<?php

declare(strict_types=1);

namespace Webovac\Core\Model;

use Nette\Application\UI\Component;


interface Linkable extends ICmsEntity
{
	function getPageName(?string $context = null): string;
	function getParameters(?string $context = null): array;
	function getLink(Component $component, ?string $context = null): string;
	function redirectToDetail(Component $component, ?string $context = null): void;
}
