<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Parameter;

use Build\Model\Page\Page;
use Build\Model\Parameter\Parameter;
use Build\Model\Parameter\ParameterData;


trait CoreParameterRepository
{
	public function getByData(ParameterData|string $data, Page $page): ?Parameter
	{
		return $this->getBy(['page' => $page, 'query' => $data instanceof ParameterData ? $data->query : $data]);
	}
}
