<?php

declare(strict_types=1);

namespace Webovac\Core\Control\MenuItem;

use App\Control\BaseTemplate;
use App\Model\Page\PageData;
use App\Model\PageTranslation\PageTranslationData;
use App\Model\Web\WebData;
use Nextras\Orm\Entity\IEntity;


class MenuItemTemplate extends BaseTemplate
{
	public PageData $pageData;
	public ?PageTranslationData $pageTranslationData;
	public WebData $webData;
	public ?IEntity $entity;
	public PageData $activePageData;
	public string $context;
	public ?string $href;
	public string $class;
	public string $tag;
	public bool $iconHasWrapper;
	public string $iconStyle;
}