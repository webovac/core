<?php

declare(strict_types=1);

namespace Webovac\Core;

use Stepapo\Utils\Service;


interface Module extends Service
{
	public static function getModuleName(): string;
	public static function getCliSetup(): array;
}