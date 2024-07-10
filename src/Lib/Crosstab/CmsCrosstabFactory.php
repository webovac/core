<?php

declare(strict_types=1);

namespace Webovac\Core\Lib\Dataset;

use App\Model\Orm;
use Nextras\Orm\Collection\ICollection;
use Stepapo\Crosstab\Crosstab;
use Stepapo\Crosstab\Control\Crosstab\CrosstabControl;


class CmsCrosstabFactory
{
	public function __construct(
		private Orm $orm,
	) {}


	public function create(string $file, array $params = []): CrosstabControl
	{
		$crosstab = Crosstab::createFromNeon($file, $params);
		return new CrosstabControl($crosstab, $this->orm);
	}
}
