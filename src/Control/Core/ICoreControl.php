<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Core;


use App\Model\Language\LanguageData;
use App\Model\Page\PageData;
use App\Model\Web\WebData;
use Webovac\Core\Model\CmsEntity;

interface ICoreControl
{
	function create(
		WebData $webData,
		LanguageData $languageData,
		?PageData $pageData,
		?PageData $navigationPageData,
		?PageData $buttonsPageData,
		?CmsEntity $entity = null,
		?CmsEntity $parentEntity = null
	): CoreControl;
}
