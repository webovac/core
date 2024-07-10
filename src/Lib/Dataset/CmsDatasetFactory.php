<?php

declare(strict_types=1);

namespace Webovac\Core\Lib\Dataset;

use Contributte\ImageStorage\ImageStorage;
use Stepapo\Dataset\Control\Dataset\DatasetControl;
use Stepapo\Dataset\Dataset;
use Stepapo\Dataset\DatasetView;
use Webovac\Core\Lib\CmsTranslator;


class CmsDatasetFactory
{
	public function __construct(
		private CmsTranslator $translator,
		private ImageStorage $imageStorage,
	) {}


	public function create(string $file, array $params = []): DatasetControl
	{
		$dataset = Dataset::createFromNeon($file, $params);
		return new DatasetControl($dataset);
	}
}
