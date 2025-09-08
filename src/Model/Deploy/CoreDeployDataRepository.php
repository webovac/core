<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Deploy;

use App\Model\Deploy\DeployData;
use Nette\Caching\Cache;
use Nextras\Orm\Collection\ICollection;


trait CoreDeployDataRepository
{
	public function getLastDeployData(): ?DeployData
	{
		return $this->cache->load('lastDeployData', function () {
			return $this->getOrmRepository()->findAll()->orderBy('createdAt', ICollection::DESC)->fetch()?->getData();
		});
	}
}
