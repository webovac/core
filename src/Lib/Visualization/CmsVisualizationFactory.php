<?php

declare(strict_types=1);

namespace Webovac\Core\Lib\Visualization;

use Build\Model\Orm;
use Stepapo\Utils\Service;
use Stepapo\Visualization\Control\Visualization\VisualizationControl;
use Stepapo\Visualization\Visualization;


class CmsVisualizationFactory implements Service
{
	public function __construct(
		private Orm $orm,
	) {}


	public function create(string $file, array $params = []): VisualizationControl
	{
		return new VisualizationControl(Visualization::createFromNeon($file, $params), $this->orm);
	}
}
