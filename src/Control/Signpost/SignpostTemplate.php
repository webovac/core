<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Signpost;

use App\Control\BaseTemplate;
use App\Model\Page\PageData;
use App\Model\Web\WebData;
use Stepapo\Model\Data\Collection;
use Webovac\Core\Model\CmsEntity;


class SignpostTemplate extends BaseTemplate
{
	public WebData $webData;
	public ?CmsEntity $entity;
	/** @var Collection<PageData> */ public Collection $pageDatas;
}
