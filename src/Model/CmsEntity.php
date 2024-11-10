<?php

declare(strict_types=1);

namespace Webovac\Core\Model;

use Stepapo\Model\Orm\StepapoEntity;
use Webovac\Core\Lib\DataProvider;


abstract class CmsEntity extends StepapoEntity
{
	protected DataProvider $dataProvider;


	public function injectDataProvider(DataProvider $dataProvider): void
	{
		$this->dataProvider = $dataProvider;
	}


	protected function getOmittedProperties(): array
	{
		return ['id', 'createdByPerson', 'updatedByPerson', 'createdAt', 'updatedAt'];
	}
}
