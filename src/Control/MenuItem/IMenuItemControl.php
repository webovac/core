<?php

declare(strict_types=1);

namespace Webovac\Core\Control\MenuItem;

use App\Model\Language\LanguageData;
use App\Model\Page\PageData;
use App\Model\Web\WebData;
use Webovac\Core\Core;
use Webovac\Core\Model\CmsEntity;

interface IMenuItemControl
{
	function create(
		PageData $pageData,
		WebData $webData,
		LanguageData $languageData,
		?CmsEntity $entity,
		?PageData $activePageData,
		string $context,
		bool $checkActive = true,
		string $moduleClass = Core::class,
		string $templateName = 'default',
	): MenuItemControl;
}