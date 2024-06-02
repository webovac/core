<?php

namespace Webovac\Core\Lib\Dataset;

use Stepapo\Dataset\UI\Dataset\Dataset;


class CmsDatasetFactory
{
	public function __construct() {}


	public function create(string $file, array $params = []): Dataset
	{
		$dataset = Dataset::createFromNeon($file, $params);
		if (isset($dataset->getViews()['table'])) {
			$dataset->getViews()['table']
				->setSortingTemplate(__DIR__ . '/templates/sorting.latte')
				->setDatasetTemplate(__DIR__ . '/templates/dataset.latte')
				->setFilterTemplate(__DIR__ . '/templates/filter.latte')
				->setDisplayTemplate(__DIR__ . '/templates/display.latte')
				->setPaginationTemplate(__DIR__ . '/templates/pagination.latte')
				->setSearchTemplate(__DIR__ . '/templates/search.latte')
				->setItemTemplate(__DIR__ . '/templates/item.latte');
		}
		if (isset($dataset->getViews()['list'])) {
			$dataset->getViews()['list']
				->setFilterTemplate(__DIR__ . '/templates/filter.latte')
				->setDisplayTemplate(__DIR__ . '/templates/display.latte')
				->setPaginationTemplate(__DIR__ . '/templates/pagination.latte')
				->setSearchTemplate(__DIR__ . '/templates/search.latte')
				->setItemListTemplate(__DIR__ . '/templates/itemList.latte');
		}
		return $dataset;
	}
}
