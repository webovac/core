<?php

declare(strict_types=1);

namespace Webovac\Core\Model\File;

use Nextras\Orm\Relationships\IRelationshipCollection;


trait HasFilesTrait
{
	public function getFiles(): IRelationshipCollection
	{
		return $this->files;
	}
}