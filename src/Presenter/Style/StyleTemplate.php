<?php

declare(strict_types=1);

namespace Webovac\Core\Presenter\Style;

use Build\Control\BaseTemplate;
use Build\Model\Layout\LayoutData;
use Build\Model\Theme\ThemeData;
use Build\Model\Web\WebData;


class StyleTemplate extends BaseTemplate
{
	public ?WebData $webData;
	public string $backgroundUrl;
	public array $colors;
	public LayoutData|array $l;
	public ThemeData|array $t;
}
