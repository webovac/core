<?php

namespace Webovac\Core\Model;

use App\Model\Language\LanguageData;
use Nette\Application\UI\Component;


interface HasSlugHistory
{
	function checkForRedirect(array $id, LanguageData $languageData, Component $component): void;
}