<?php

declare(strict_types=1);

namespace Webovac\Core\Lib\Dataset;

use Stepapo\Dataset\Control\Dataset\DatasetControl;
use Stepapo\Dataset\Dataset;
use Stepapo\Utils\Service;


class CmsDatasetFactory implements Service
{
	public function create(string $file, array $params = []): DatasetControl
	{
		return new DatasetControl(Dataset::createFromNeon($file, $params));
	}
}
