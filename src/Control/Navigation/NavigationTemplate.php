<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Navigation;

use App\Control\BaseTemplate;
use App\Model\DataModel;
use App\Model\Layout\LayoutData;
use App\Model\Page\PageData;
use App\Model\Web\WebData;
use Nextras\Orm\Entity\IEntity;
use Stepapo\Model\Data\Collection;
use Webovac\Core\Model\CmsEntity;


class NavigationTemplate extends BaseTemplate
{
	public ?string $title;
	public WebData $webData;
	public LayoutData $layoutData;
	public PageData $activePageData;
	public DataModel $dataModel;
	public ?CmsEntity $entity;
	/** @var Collection<PageData> */ public Collection $pageDatas;
	/** @var IEntity[] */ public array $entityMenuItems;
}
