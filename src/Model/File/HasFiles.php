<?php

declare(strict_types=1);

namespace Webovac\Core\Model\File;

use Nextras\Orm\Relationships\IRelationshipCollection;
use Webovac\Core\Model\ICmsEntity;


interface HasFiles extends ICmsEntity
{
	/** @returns IRelationshipCollection<File> */
	function getFiles(): IRelationshipCollection;
}
