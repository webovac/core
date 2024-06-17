<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Navigation;

use App\Control\BaseTemplate;
use App\Model\Language\LanguageData;
use App\Model\Layout\LayoutData;
use App\Model\Page\PageData;
use Webovac\Core\Lib\Collection;
use Webovac\Core\Model\CmsEntity;


class NavigationTemplate extends BaseTemplate
{
	public ?string $title;
	public ?PageData $pageData;
	public LanguageData $languageData;
	public LayoutData $layoutData;
	public ?CmsEntity $entity;
	public PageData $activePageData;
	/** @var Collection<PageData> */ public Collection $pageDatas;
}
