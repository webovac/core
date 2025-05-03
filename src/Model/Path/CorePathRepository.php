<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Path;

use App\Model\PageTranslation\PageTranslation;
use App\Model\Path\Path;
use App\Model\Path\PathData;
use App\Model\Web\Web;


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


	public function getPath(string $p, Web $web, ?Path $path = null, string $separator = '-', int $num = 1): string
	{
		$p = $p . ($num > 1 ? '-' . $num : '');
		$filter = [
			'path' => $p,
			'pageTranslation->page->web' => $web,
		];
		$filter['active'] = true;
		if ($path) {
			$filter['id!='] = $path->id;
		}
		if ($this->getBy($filter)) {
			return $this->getPath($p, $web, $path, $separator, $num + 1);
		}
		return $separator === '-' ? $p : str_replace('-', $separator, $p);
	}
}
