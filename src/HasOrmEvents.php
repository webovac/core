<?php

declare(strict_types=1);

namespace Webovac\Core;


interface HasOrmEvents extends Module
{
	function registerOrmEvents(): void;
}
