<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Buttons;

use Build\Control\BaseTemplate;
use Build\Model\Page\PageData;
use Build\Model\Web\WebData;
use Stepapo\Model\Data\Collection;
use Webovac\Core\Model\CmsEntity;


class ButtonsTemplate extends BaseTemplate
{
	public PageData $pageData;
	public WebData $webData;
	public ?CmsEntity $entity;
	/** @var Collection<PageData> */ public Collection $pageDatas;
}
