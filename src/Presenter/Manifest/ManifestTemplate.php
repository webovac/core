<?php

declare(strict_types=1);

namespace Webovac\Core\Presenter\Manifest;

use App\Control\BaseTemplate;
use App\Model\Web\WebData;
use App\Model\WebTranslation\WebTranslationData;


class ManifestTemplate extends BaseTemplate
{
	public ?WebData $webData;
	public ?WebTranslationData $webTranslationData;
}
