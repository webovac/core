<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Navigation;

use App\Control\BaseTemplate;
use App\Model\Language\LanguageData;
use App\Model\Layout\LayoutData;
use App\Model\Page\PageData;
use Nextras\Orm\Entity\IEntity;
use Webovac\Core\Lib\Collection;


class NavigationTemplate extends BaseTemplate
{
	public ?string $title;
	public ?PageData $pageData;
	public LanguageData $languageData;
	public LayoutData $layoutData;
	public ?IEntity $entity;
	public PageData $activePageData;
	/** @var Collection<PageData> */ public Collection $pageDatas;
}
