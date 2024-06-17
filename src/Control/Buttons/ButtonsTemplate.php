<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Buttons;

use App\Control\BaseTemplate;
use App\Model\Language\LanguageData;
use App\Model\Layout\LayoutData;
use App\Model\Page\Page;
use App\Model\Page\PageData;
use Webovac\Core\Lib\Collection;
use Webovac\Core\Model\CmsEntity;


class ButtonsTemplate extends BaseTemplate
{
	public Page $page;
	/** @var Collection<PageData> */ public Collection $pageDatas;
	public LanguageData $languageData;
	public LayoutData $layoutData;
	public ?CmsEntity $entity;
}
