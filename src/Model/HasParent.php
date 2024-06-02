<?php

declare(strict_types=1);

namespace Webovac\Core\Model;

use App\Model\Language\LanguageData;


interface HasParent
{
	function getParentParameter(?LanguageData $language = null);
}
