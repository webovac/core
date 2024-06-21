<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Buttons;

use App\Model\Language\LanguageData;
use App\Model\Page\PageData;
use App\Model\Web\WebData;
use Webovac\Core\Factory;
use Webovac\Core\Model\CmsEntity;


interface IButtonsControl extends Factory
{
	function create(WebData $webData, ?PageData $pageData, LanguageData $languageData, ?CmsEntity $entity): ButtonsControl;
}
