<?php

declare(strict_types=1);

namespace Webovac\Core\Lib\Dataset;

use App\Model\Orm;
use Stepapo\Crosstab\Crosstab;
use Stepapo\Crosstab\Control\Crosstab\CrosstabControl;


class CmsCrosstabFactory
{
	public function __construct(
		private Orm $orm,
	) {}


	public function create(string $file, array $params = []): CrosstabControl
	{
		return new CrosstabControl(Crosstab::createFromNeon($file, $params), $this->orm);
	}
}
