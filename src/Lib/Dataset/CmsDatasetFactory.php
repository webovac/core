<?php

declare(strict_types=1);

namespace Webovac\Core\Lib\Dataset;

use Contributte\ImageStorage\ImageStorage;
use Stepapo\Dataset\Control\Dataset\DatasetControl;
use Stepapo\Dataset\Dataset;
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
		$dataset->translator = $this->translator;
		if (isset($dataset->views['table'])) {
			$tableView = $dataset->views['table'];
			$tableView->sortingTemplate = __DIR__ . '/templates/sorting.latte';
			$tableView->datasetTemplate =__DIR__ . '/templates/dataset.latte';
			$tableView->filterTemplate =__DIR__ . '/templates/filter.latte';
			$tableView->displayTemplate =__DIR__ . '/templates/display.latte';
			$tableView->paginationTemplate =__DIR__ . '/templates/pagination.latte';
			$tableView->searchTemplate = __DIR__ . '/templates/search.latte';
			$tableView->itemTemplate = __DIR__ . '/templates/item.latte';
		}
		if (isset($dataset->views['list'])) {
			$listView = $dataset->views['table'];
			$listView->filterTemplate = __DIR__ . '/templates/filter.latte';
			$listView->displayTemplate = __DIR__ . '/templates/display.latte';
			$listView->paginationTemplate = __DIR__ . '/templates/pagination.latte';
			$listView->searchTemplate = __DIR__ . '/templates/search.latte';
			$listView->itemListTemplate = __DIR__ . '/templates/itemList.latte';
		}
		return new DatasetControl($dataset);
	}
}
