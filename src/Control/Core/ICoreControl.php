<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Core;


use App\Model\Language\LanguageData;
use App\Model\Page\PageData;
use App\Model\Web\WebData;
use Nextras\Orm\Entity\IEntity;

interface ICoreControl
{
	function create(
		WebData $webData,
		LanguageData $languageData,
		?PageData $pageData,
		?PageData $navigationPageData,
		?PageData $buttonsPageData,
		?IEntity $entity = null,
		?IEntity $parentEntity = null
	): CoreControl;
}
