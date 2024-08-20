<?php

declare(strict_types=1);

namespace Webovac\Core\Control\MenuItem;

use App\Control\BaseTemplate;
use App\Model\Language\LanguageData;
use App\Model\Page\PageData;
use App\Model\PageTranslation\PageTranslationData;


class MenuItemTemplate extends BaseTemplate
{
	public PageData $pageData;
	public ?PageTranslationData $pageTranslationData;
	public LanguageData $languageData;
	public LanguageData $targetLanguageData;
	public ?string $title;
	public string $context;
	public ?string $href;
	public string $class;
	public string $tag;
	public bool $iconHasWrapper;
	public string $iconStyle;
}