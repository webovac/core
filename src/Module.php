<?php

declare(strict_types=1);

namespace Webovac\Core;


interface Module
{
	public static function getModuleName(): string;
}