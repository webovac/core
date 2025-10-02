<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Menu;

use Build\Control\BaseTemplate;
use Build\Model\DataModel;
use Build\Model\Language\LanguageData;
use Build\Model\Layout\LayoutData;
use Build\Model\Page\PageData;
use Build\Model\Theme\ThemeData;
use Build\Model\Web\WebData;
use Stepapo\Model\Data\Collection;
use Webovac\Core\Lib\PageActivator;
use Webovac\Core\Model\CmsEntity;


class MenuTemplate extends BaseTemplate
{
	public WebData $webData;
	public string $logoUrl;
	public PageData $pageData;
	public LanguageData $languageData;
	/** @var Collection<PageData> */ public Collection $pageDatas;
	public LayoutData $layoutData;
	public ?PageData $homePageData;
	public DataModel $dataModel;
	public ?CmsEntity $entity;
	public string $title;
	public string $wwwDir;
	public bool $isError;
	public bool $hasSearch;
	/** @var string[] */ public array $availableTranslations;
	/** @var ThemeData[] */ public Collection $themeDatas;
	public PageActivator $pageActivator;
}
