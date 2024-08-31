<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Menu;

use App\Control\BaseTemplate;
use App\Model\DataModel;
use App\Model\Language\LanguageData;
use App\Model\Layout\LayoutData;
use App\Model\Page\PageData;
use App\Model\Theme\ThemeData;
use App\Model\Web\WebData;
use Stepapo\Utils\Model\Collection;
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
}
