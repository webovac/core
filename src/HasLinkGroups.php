<?php

declare(strict_types=1);

namespace Webovac\Core;


interface HasLinkGroups extends Module
{
	function getLinkGroups(): array;
}