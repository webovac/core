<?php

declare(strict_types=1);

namespace Webovac\Core\Control\PageItem;

use App\Control\BaseTemplate;
use App\Model\Language\LanguageData;
use App\Model\Page\Page;


class PageItemTemplate extends BaseTemplate
{
	public Page $page;
	public LanguageData $languageData;
	public ?int $siblingCount;
}
