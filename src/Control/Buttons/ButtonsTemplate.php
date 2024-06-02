<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Buttons;

use App\Control\BaseTemplate;
use App\Model\Language\Language;
use App\Model\Page\Page;
use Nextras\Orm\Collection\ICollection;
use Nextras\Orm\Entity\IEntity;


class ButtonsTemplate extends BaseTemplate
{
	public Page $page;

	public ICollection $childPages;

	public Language $language;

	public ?IEntity $entity;
}
