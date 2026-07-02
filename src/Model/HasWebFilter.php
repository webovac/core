<?php

declare(strict_types=1);

namespace Webovac\Core\Model;

use Build\Model\Web\WebData;


interface HasWebFilter extends ICmsRepository
{
	function getWebFilter(WebData $webData): ?array;

	function shouldFilterByWeb(WebData $webData): bool;
}
