<?php

declare(strict_types=1);

namespace Webovac\Core\Model;

use Nette\DI\Attributes\Inject;
use Nextras\Orm\Exception\NotSupportedException;
use Stepapo\Model\Orm\StepapoEntity;
use Stepapo\Utils\Injectable;
use Webovac\Core\Lib\DataProvider;


abstract class CmsEntity extends StepapoEntity implements Injectable
{
	#[Inject] public DataProvider $dataProvider;


	protected function getOmittedProperties(): array
	{
		return [/*'id', */'createdByPerson', 'updatedByPerson', 'createdAt', 'updatedAt'];
	}


	public function getTitle(): string
	{
		if (!$this->getMetadata()->hasProperty('title')) {
			throw new NotSupportedException();
		}
		return $this->title;
	}
}
