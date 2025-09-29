<?php

declare(strict_types=1);

namespace Webovac\Core\Model\File;

use Nextras\Orm\Relationships\IRelationshipCollection;


interface HasFiles
{
	function getFiles(): IRelationshipCollection;
}