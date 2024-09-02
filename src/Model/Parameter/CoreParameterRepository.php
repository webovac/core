<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Parameter;

use App\Model\Page\Page;
use App\Model\Parameter\Parameter;
use App\Model\Parameter\ParameterData;


trait CoreParameterRepository
{
	public function getByData(ParameterData|string $data, Page $page): ?Parameter
	{
		return $this->getBy(['page' => $page, 'query' => $data instanceof ParameterData ? $data->query : $data]);
	}
}
