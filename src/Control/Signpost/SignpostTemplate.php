<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Signpost;

use Build\Control\BaseTemplate;
use Build\Model\Page\PageData;
use Build\Model\Web\WebData;
use Stepapo\Model\Data\Collection;
use Webovac\Core\Model\CmsEntity;


class SignpostTemplate extends BaseTemplate
{
	public WebData $webData;
	public PageData $pageData;
	public ?CmsEntity $entity;
	/** @var Collection<PageData> */ public Collection $pageDatas;
}
