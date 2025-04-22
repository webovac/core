<?php

namespace Webovac\Core\Model\File;

use Nextras\Orm\Relationships\IRelationshipCollection;


interface HasFiles
{
	function getFiles(): IRelationshipCollection;
}