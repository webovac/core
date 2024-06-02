<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Navigation;

use App\Control\BaseTemplate;
use App\Model\Language\Language;
use App\Model\Page\Page;
use Nextras\Orm\Collection\ICollection;
use Nextras\Orm\Entity\IEntity;


class NavigationTemplate extends BaseTemplate
{
	public ?string $title;
	public Page $page;
	public Language $language;
	public ?IEntity $entity;
	/** @var ICollection<Page>|array */ public ICollection|array $childPages;
}
