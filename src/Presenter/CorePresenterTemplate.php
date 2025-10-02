<?php

declare(strict_types=1);

namespace Webovac\Core\Presenter;

use Build\Model\Deploy\DeployData;
use Build\Model\Language\LanguageData;
use Build\Model\Page\PageData;
use Build\Model\PageTranslation\PageTranslation;
use Build\Model\PageTranslation\PageTranslationData;
use Build\Model\Web\WebData;
use Build\Model\WebTranslation\WebTranslationData;
use Stepapo\Model\Data\Collection;
use Webovac\Core\Model\CmsEntity;


trait CorePresenterTemplate
{
	public LanguageData $languageData;
	public WebData $webData;
	public WebTranslationData $webTranslationData;
	public PageData $pageData;
	public ?DeployData $deployData;
	public ?string $imageUrl;
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
	public ?string $description;
	public string $metaType;
	public string $metaUrl;
	public array $bodyClasses;
	public bool $emptyNavigation;
}