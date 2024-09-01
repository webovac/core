<?php

declare(strict_types=1);

namespace Webovac\Core\Definition;


interface DefinitionProcessor
{
	function process(Definition $structure): int;
}