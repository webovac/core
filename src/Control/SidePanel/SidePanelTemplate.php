<?php

declare(strict_types=1);

namespace Webovac\Core\Control\SidePanel;

use Build\Control\BaseTemplate;
use Build\Model\DataModel;
use Build\Model\Language\LanguageData;
use Build\Model\Layout\LayoutData;
use Build\Model\Module\ModuleData;
use Build\Model\Page\PageData;
use Build\Model\Theme\ThemeData;
use Build\Model\Web\WebData;
use Stepapo\Model\Data\Collection;
use Webovac\Core\Model\CmsEntity;


class SidePanelTemplate extends BaseTemplate
{
	public WebData $webData;
	public PageData $pageData;
	public LanguageData $languageData;
	public LayoutData $layoutData;
	/** @var ThemeData[] */ public Collection $themeDatas;
	public DataModel $dataModel;
	public ?CmsEntity $entity;
	public bool $isError;
	public bool $hasSearch;
	public bool $hasPersons;
	/** @var string[] */ public array $availableTranslations;
	public bool $showAdmin;
	public string $adminLang;
	public ?ModuleData $pageModuleData;
	public array $languageShortcuts;
}
