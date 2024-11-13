<?php

declare(strict_types=1);

namespace Webovac\Core;


interface HasOrmEvents
{
	public function registerOrmEvents(): void;
}