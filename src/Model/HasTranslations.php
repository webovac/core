<?php

declare(strict_types=1);

namespace Webovac\Core\Model;

use Build\Model\Language\LanguageData;


interface HasTranslations extends ICmsEntity
{
	function getTranslation(LanguageData $language): ?Translation;
}
