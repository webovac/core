<?php

declare(strict_types=1);

namespace Webovac\Core\Control\SidePanel;

use App\Control\BaseTemplate;
use App\Model\DataModel;
use App\Model\Language\LanguageData;
use App\Model\Layout\LayoutData;
use App\Model\Page\PageData;
use App\Model\Web\WebData;
use Nextras\Orm\Entity\IEntity;


class SidePanelTemplate extends BaseTemplate
{
	public WebData $webData;
	public PageData $pageData;
	public LanguageData $languageData;
	public LayoutData $layoutData;
	public DataModel $dataModel;
	public ?IEntity $entity;
	public bool $isError;
	public bool $hasSearch;
	/** @var array<string> */ public array $availableTranslations;
}
