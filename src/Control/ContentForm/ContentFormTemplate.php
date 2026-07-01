<?php

declare(strict_types=1);

namespace Webovac\Core\Control\ContentForm;

use Build\Control\BaseTemplate;
use Webovac\Core\Model\HasTranslations;


class ContentFormTemplate extends BaseTemplate
{
	public HasTranslations $hasTranslations;
	public string $lang;
	public string $mentions;
	public string $linkGroups;
}
