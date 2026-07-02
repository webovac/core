<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Menu;

use Build\Control\BaseTemplate;
use Build\Model\DataModel;
use Build\Model\Language\LanguageData;
use Build\Model\Layout\LayoutData;
use Build\Model\Module\ModuleData;
use Build\Model\Page\PageData;
use Build\Model\Theme\ThemeData;
use Build\Model\Web\WebData;
use Stepapo\Model\Data\Collection;
use Webovac\Core\Lib\FileUploader;
use Webovac\Core\Lib\PageActivator;
use Webovac\Core\Lib\PageRequirementChecker;
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
	public ?PageData $menuPageData;
	public DataModel $dataModel;
	public ?CmsEntity $entity;
	public string $title;
	public string $wwwDir;
	public bool $isError;
	public bool $hasSearch;
	/** @var string[] */ public array $availableTranslations;
	/** @var Collection<ThemeData> */ public Collection $themeDatas;
	public PageActivator $pageActivator;
	public PageRequirementChecker $requirementChecker;
	public FileUploader $fileUploader;
	public LanguageData $defaultLanguageData;
	public bool $showSearch;
	public bool $hasPersons;
	public bool $showAdmin;
	public array $languageShortcuts;
	public ?ModuleData $pageModuleData;
	public string $adminLang;
}
