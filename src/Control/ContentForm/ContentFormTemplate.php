<?php

declare(strict_types=1);

namespace Webovac\Core\Control\ContentForm;

use Build\Control\BaseTemplate;
use Build\Model\Page\Page;


class ContentFormTemplate extends BaseTemplate
{
	public Page $page;
	public string $lang;
	public string $mentions;
	public string $linkGroups;
}
