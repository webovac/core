<?php

declare(strict_types=1);

namespace Webovac\Core\Model\PageTranslation;

use App\Model\Path\Path;
use App\Model\Web\Web;


trait CorePageTranslation
{
	public function getActivePath(Web $web): ?Path
	{
		return $this->paths->toCollection()->getBy(['web' => $web, 'active' => true]);
	}
}
