<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Menu;

use App\Control\BaseTemplate;
use App\Model\Language\Language;
use App\Model\Page\Page;
use App\Model\Web\Web;
use Nextras\Orm\Collection\ICollection;
use Nextras\Orm\Entity\IEntity;


class MenuTemplate extends BaseTemplate
{
	public Web $web;
	public Page $page;
	public Language $language;
	public ?IEntity $entity;
	public string $wwwDir;
	/** @var ICollection<Page>|array */ public ICollection|array $pages;
	/** @var array<string> */public array $availableTranslations;
}
