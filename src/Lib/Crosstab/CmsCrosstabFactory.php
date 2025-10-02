<?php

declare(strict_types=1);

namespace Webovac\Core\Lib\Crosstab;

use Build\Model\Orm;
use Stepapo\Crosstab\Control\Crosstab\CrosstabControl;
use Stepapo\Crosstab\Crosstab;
use Stepapo\Utils\Service;


class CmsCrosstabFactory implements Service
{
	public function __construct(
		private Orm $orm,
	) {}


	public function create(string $file, array $params = []): CrosstabControl
	{
		return new CrosstabControl(Crosstab::createFromNeon($file, $params), $this->orm);
	}
}
