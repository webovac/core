<?php

namespace Webovac\Core\Definition;


interface DefinitionProcessor
{
	function process(Definition $structure): int;
}