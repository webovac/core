<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Path;

use App\Model\Page\Page;
use App\Model\PageTranslation\PageTranslation;
use App\Model\Path\Path;
use App\Model\Path\PathData;


trait CorePathRepository
{
	public function getByData(PathData|string $data, PageTranslation $pageTranslation): ?Path
	{
		return $this->getBy([
			'pageTranslation' => $pageTranslation,
			'web' => $data->web,
			'path' => $data->path,
		]);
	}
}
