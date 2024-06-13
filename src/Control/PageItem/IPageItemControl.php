<?php

declare(strict_types=1);

namespace Webovac\Core\Control\PageItem;

use App\Model\Language\LanguageData;
use App\Model\Page\Page;
use Webovac\Core\Core;


interface IPageItemControl
{
	function create(
		Page $page,
		LanguageData $languageData,
		string $moduleClass = Core::class,
		string $templateName = 'default',
	): PageItemControl;
}
