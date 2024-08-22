<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Buttons;

use App\Control\BaseTemplate;
use App\Model\DataModel;
use App\Model\Language\LanguageData;
use App\Model\Page\PageData;
use App\Model\Web\WebData;
use Latte\Attributes\TemplateFunction;
use Stepapo\Utils\Model\Collection;
use Webovac\Core\Control\MenuItem\MenuItemTemplate;
use Webovac\Core\Model\CmsEntity;


class ButtonsTemplate extends BaseTemplate
{
	public PageData $pageData;
	public WebData $webData;
	public LanguageData $languageData;
	public ?CmsEntity $entity;
	/** @var Collection<PageData> */ public Collection $pageDatas;
}
