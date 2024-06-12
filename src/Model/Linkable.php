<?php

declare(strict_types=1);

namespace Webovac\Core\Model;

use App\Model\Language\LanguageData;


interface Linkable
{
	function getParameter(?LanguageData $language = null);
}
