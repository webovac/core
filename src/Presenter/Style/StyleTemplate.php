<?php

declare(strict_types=1);

namespace Webovac\Core\Presenter\Style;

use App\Control\BaseTemplate;
use App\Model\Layout\LayoutData;
use App\Model\Web\WebData;


class StyleTemplate extends BaseTemplate
{
	public ?WebData $webData;
	public string $backgroundUrl;
	public array $colors;
	public LayoutData $l;
}
