<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Signpost;

use App\Control\BaseTemplate;
use App\Model\Language\LanguageData;
use App\Model\Layout\LayoutData;
use App\Model\Page\PageData;
use Nextras\Orm\Entity\IEntity;
use Webovac\Core\Lib\Collection;


class SignpostTemplate extends BaseTemplate
{
	public PageData $pageData;
	/** @var Collection<PageData> */ public Collection $pageDatas;
	public LanguageData $languageData;
	public LayoutData $layoutData;
	public ?IEntity $entity;
}
