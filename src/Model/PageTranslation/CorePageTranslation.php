<?php

declare(strict_types=1);

namespace Webovac\Core\Model\PageTranslation;

use Build\Model\Path\Path;
use Build\Model\Web\Web;
use Stepapo\Model\Orm\AuditableTrait;
use Webovac\Core\Model\HasContentTrait;


trait CorePageTranslation
{
	use AuditableTrait;
	use HasContentTrait;

	public function getActivePath(Web $web): ?Path
	{
		return $this->paths->toCollection()->getBy(['web' => $web, 'active' => true]);
	}
}
