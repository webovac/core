<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Buttons;

use App\Control\BaseTemplate;
use App\Model\Language\LanguageData;
use App\Model\Layout\LayoutData;
use App\Model\Page\Page;
use App\Model\Page\PageData;
use Nextras\Orm\Entity\IEntity;
use Webovac\Core\Lib\Collection;


class ButtonsTemplate extends BaseTemplate
{
	public Page $page;
	/** @var Collection<PageData> */ public Collection $pageDatas;
	public LanguageData $languageData;
	public LayoutData $layoutData;
	public ?IEntity $entity;
}
