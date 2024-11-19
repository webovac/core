<?php

declare(strict_types=1);

namespace Webovac\Core;

use App\Model\Language\Language;


class IndexTranslationDefinition
{
	public ?Language $language = null;
	public array $documents;
}