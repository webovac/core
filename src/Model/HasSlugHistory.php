<?php

declare(strict_types=1);

namespace Webovac\Core\Model;

use Build\Model\Language\LanguageData;
use Build\Model\Page\PageData;
use Nette\Application\UI\Component;


interface HasSlugHistory
{
	function checkForRedirect(array $id, PageData $pageData, LanguageData $languageData, Component $component): void;
}