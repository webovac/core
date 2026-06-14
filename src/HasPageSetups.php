<?php

declare(strict_types=1);

namespace Webovac\Core;


interface HasPageSetups extends Module
{
	function getPageSetups(): array;
}