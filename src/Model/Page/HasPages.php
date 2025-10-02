<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Page;

use Build\Model\Page\Page;
use Nextras\Orm\Collection\ICollection;
use Nextras\Orm\Entity\IEntity;


interface HasPages extends IEntity
{
	/** @return ICollection<Page> */ function getPages(): ICollection;
	/** @return ICollection<Page> */ function getPagesForMenu(): ICollection;
}
