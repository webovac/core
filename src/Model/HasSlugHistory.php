<?php

namespace Webovac\Core\Model;

use App\Model\Language\LanguageData;
use App\Model\Page\PageData;
use Nette\Application\UI\Component;


interface HasSlugHistory
{
	function checkForRedirect(array $id, PageData $pageData, LanguageData $languageData, Component $component): void;
}