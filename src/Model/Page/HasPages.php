<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Page;

use Build\Model\Page\Page;
use Nextras\Orm\Collection\ICollection;
use Webovac\Core\Model\ICmsEntity;


interface HasPages extends ICmsEntity
{
	/** @return ICollection<Page> */ function getPages(): ICollection;
	/** @return ICollection<Page> */ function getPagesForMenu(): ICollection;
}
