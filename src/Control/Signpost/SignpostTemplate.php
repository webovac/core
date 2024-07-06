<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Signpost;

use App\Control\BaseTemplate;
use App\Model\Page\PageData;
use Stepapo\Utils\Model\Collection;


class SignpostTemplate extends BaseTemplate
{
	/** @var Collection<PageData> */ public Collection $pageDatas;
}
