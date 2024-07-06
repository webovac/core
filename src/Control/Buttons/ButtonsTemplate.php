<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Buttons;

use App\Control\BaseTemplate;
use App\Model\Page\PageData;
use Stepapo\Utils\Model\Collection;


class ButtonsTemplate extends BaseTemplate
{
	/** @var Collection<PageData> */ public Collection $pageDatas;
}
