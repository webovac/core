<?php

declare(strict_types=1);

namespace Webovac\Core;


interface HasModuleSetups extends Module
{
	function getModuleSetups(): array;
}