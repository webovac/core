<?php

declare(strict_types=1);

namespace Webovac\Core\Control\SidePanel;

use App\Control\BaseTemplate;
use App\Model\DataModel;
use App\Model\Language\LanguageData;
use App\Model\Layout\LayoutData;
use App\Model\Module\ModuleData;
use App\Model\Page\PageData;
use App\Model\Theme\ThemeData;
use App\Model\Web\WebData;
use Stepapo\Utils\Model\Collection;
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
