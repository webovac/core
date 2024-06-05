<?php

declare(strict_types=1);

namespace Webovac\Core\Presenter;

use App\Model\Language\LanguageData;
use App\Model\Page\PageData;
use App\Model\PageTranslation\PageTranslation;
use App\Model\PageTranslation\PageTranslationData;
use App\Model\Web\WebData;
use App\Model\WebTranslation\WebTranslationData;
use Nextras\Orm\Entity\IEntity;
use Webovac\Core\Lib\Collection;


trait CorePresenterTemplate
{
	public LanguageData $languageData;
	public WebData $webData;
	public WebTranslationData $webTranslationData;
	public PageData $pageData;
	public string $imageUrl;
	public PageTranslation $pageTranslation;
	public PageTranslationData $pageTranslationData;
	/** @var Collection<WebData> */ public Collection $webDatas;
	public bool $hasSideMenu;
	public ?IEntity $entity;
	public ?string $entityName;
	public string $title;
	public string $metaTitle;
	public string $metaType;
	public string $metaUrl;
	public bool $showAdmin;
	public array $bodyClasses;
}