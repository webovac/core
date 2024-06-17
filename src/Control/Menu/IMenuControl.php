<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Menu;

use App\Model\Language\LanguageData;
use App\Model\Page\PageData;
use App\Model\Web\WebData;
use Webovac\Core\Model\CmsEntity;


interface IMenuControl
{
	function create(WebData $webData, PageData $pageData, LanguageData $languageData, ?CmsEntity $entity): MenuControl;
}
