<?php

declare(strict_types=1);

namespace Webovac\Core\Model\File;

use Build\Model\File\File;
use Nextras\Orm\Relationships\HasMany;
use Nextras\Orm\Relationships\IRelationshipCollection;


trait HasFilesTrait
{
	/** @return HasMany<File> */
	public function getFiles(): IRelationshipCollection
	{
		return $this->files;
	}
}
