<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Navigation;

use App\Control\BaseTemplate;
use App\Model\Layout\LayoutData;
use App\Model\Page\PageData;
use Stepapo\Utils\Model\Collection;


class NavigationTemplate extends BaseTemplate
{
	public ?string $title;
	public LayoutData $layoutData;
	public PageData $activePageData;
	/** @var Collection<PageData> */ public Collection $pageDatas;
}
