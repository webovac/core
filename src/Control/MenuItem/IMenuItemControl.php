<?php

declare(strict_types=1);

namespace Webovac\Core\Control\MenuItem;

use App\Model\Language\LanguageData;
use App\Model\Page\PageData;
use App\Model\Web\WebData;
use Nextras\Orm\Entity\IEntity;
use Webovac\Core\Core;

interface IMenuItemControl
{
	function create(
		PageData $pageData,
		WebData $webData,
		LanguageData $languageData,
		?IEntity $entity,
		?PageData $activePageData,
		string $context,
		bool $checkActive = true,
		string $moduleClass = Core::class,
		string $templateName = 'default',
	): MenuItemControl;
}