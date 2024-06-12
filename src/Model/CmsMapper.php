<?php

declare(strict_types=1);

namespace Webovac\Core\Model;

use Nextras\Orm\Entity\IEntity;
use Nextras\Orm\Mapper\Dbal\Conventions\Conventions;
use Nextras\Orm\Mapper\Dbal\Conventions\IConventions;
use Nextras\Orm\Mapper\Mapper;


abstract class CmsMapper extends Mapper
{
	protected function createConventions(): IConventions
	{
		$conventions = parent::createConventions();
		assert($conventions instanceof Conventions);
		$conventions->manyHasManyStorageNamePattern = '%s2%s';
		return $conventions;
	}


	public function delete(IEntity $entity): void
	{
		$this->connection->query('DELETE FROM %table WHERE id = %i', $this->getTableName(), $entity->getPersistedId());
	}
}
