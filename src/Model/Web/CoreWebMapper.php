<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Web;

use Nextras\Orm\Entity\Reflection\PropertyMetadata;
use Nextras\Orm\Mapper\Dbal\DbalMapper;


trait CoreWebMapper
{
	public function getManyHasManyParameters(PropertyMetadata $sourceProperty, DbalMapper $targetMapper): array
	{
		if ($sourceProperty->name === 'adminPersons') {
			return [
				'web2admin_person',
				['web_id', 'person_id'],
			];
		}
		if ($sourceProperty->name === 'adminRoles') {
			return [
				'web2admin_role',
				['web_id', 'role_id'],
			];
		}

		return parent::getManyHasManyParameters($sourceProperty, $targetMapper);
	}
}
