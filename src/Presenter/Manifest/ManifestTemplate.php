<?php

declare(strict_types=1);

namespace Webovac\Core\Presenter\Manifest;

use Build\Control\BaseTemplate;
use Build\Model\Web\WebData;
use Build\Model\WebTranslation\WebTranslationData;


class ManifestTemplate extends BaseTemplate
{
	public ?WebData $webData;
	public ?WebTranslationData $webTranslationData;
}
