<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Menu;

use App\Control\BaseTemplate;
use App\Model\DataModel;
use App\Model\Language\Language;
use App\Model\Language\LanguageData;
use App\Model\Page\Page;
use App\Model\Page\PageData;
use App\Model\Web\Web;
use App\Model\Web\WebData;
use Nextras\Orm\Collection\ICollection;
use Nextras\Orm\Entity\IEntity;
use Webovac\Core\Lib\Collection;


class MenuTemplate extends BaseTemplate
{
	public WebData $webData;
	public string $logoUrl;
	public PageData $pageData;
	/** @var Collection<PageData> */ public Collection $pageDatas;
	public LanguageData $languageData;
	public ?PageData $homePageData;
	public DataModel $dataModel;
	public ?IEntity $entity;
	public string $title;
	public string $wwwDir;
	public bool $isError;
	/** @var ICollection<Page>|array */ public ICollection|array $pages;
	/** @var array<string> */ public array $availableTranslations;
}
