<?php

declare(strict_types=1);

namespace Webovac\Core\Model\QueryName;

use App\Model\Page\Page;
use App\Model\PageTranslation\PageTranslation;
use App\Model\PageTranslation\PageTranslationData;
use App\Model\QueryName\QueryNameData;


trait CoreQueryNameRepository
{
	public function getByData(QueryNameData|string $data, Page $page): ?PageTranslation
	{
		return $this->getBy(['page' => $page, 'query' => $data instanceof QueryNameData ? $data->query : $data]);
	}
}
