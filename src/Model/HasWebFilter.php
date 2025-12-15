<?php

declare(strict_types=1);

namespace Webovac\Core\Model;

use Build\Model\Web\WebData;


interface HasWebFilter
{
	function getWebFilter(WebData $webData): ?array;
}