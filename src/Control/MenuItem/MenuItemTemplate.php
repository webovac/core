<?php

namespace Webovac\Core\Control\MenuItem;

use App\Control\BaseTemplate;
use App\Model\Page\PageData;
use App\Model\PageTranslation\PageTranslationData;
use Nextras\Orm\Entity\IEntity;


class MenuItemTemplate extends BaseTemplate
{
	public PageData $pageData;
	public ?PageTranslationData $pageTranslationData;
	public ?IEntity $entity;
	public PageData $activePageData;
	public string $context;
}