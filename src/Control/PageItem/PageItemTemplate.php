<?php

declare(strict_types=1);

namespace Webovac\Core\Control\PageItem;

use Build\Control\BaseTemplate;
use Build\Model\Page\Page;


class PageItemTemplate extends BaseTemplate
{
	public Page $page;
	public ?int $siblingCount;
}
