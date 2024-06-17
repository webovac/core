<?php

declare(strict_types=1);

namespace Webovac\Core\Presenter;

use App\Model\Language\LanguageData;
use App\Model\Page\PageData;
use App\Model\PageTranslation\PageTranslation;
use App\Model\PageTranslation\PageTranslationData;
use App\Model\Web\WebData;
use App\Model\WebTranslation\WebTranslationData;
use Webovac\Core\Lib\Collection;
use Webovac\Core\Model\CmsEntity;


trait CorePresenterTemplate
{
	public LanguageData $languageData;
	public WebData $webData;
	public WebTranslationData $webTranslationData;
	public PageData $pageData;
	public string $imageUrl;
	public string $smallIconUrl;
	public string $largeIconUrl;
	public PageTranslation $pageTranslation;
	public PageTranslationData $pageTranslationData;
	/** @var Collection<WebData> */ public Collection $webDatas;
	public bool $hasSideMenu;
	public ?CmsEntity $entity;
	public ?string $entityName;
	public string $title;
	public string $metaTitle;
	public string $metaType;
	public string $metaUrl;
	public bool $showAdmin;
	public array $bodyClasses;
	public string $adminLang;
	public array $languageShortcuts;
}