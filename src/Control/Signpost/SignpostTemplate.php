<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Signpost;

use App\Control\BaseTemplate;
use App\Model\Language\Language;
use App\Model\Page\Page;
use Nextras\Orm\Collection\ICollection;
use Nextras\Orm\Entity\IEntity;


class SignpostTemplate extends BaseTemplate
{
	public Page $page;

	/** @var ICollection<Page> */
	public ICollection $childPages;

	public Language $language;

	public ?IEntity $entity;
}
