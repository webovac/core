<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Signal;

use Build\Model\Page\Page;
use Build\Model\Signal\Signal;
use Build\Model\Signal\SignalData;


trait CoreSignalRepository
{
	public function getByData(SignalData|string $data, Page $page): ?Signal
	{
		return $this->getBy(['page' => $page, 'name' => $data instanceof SignalData ? $data->name : $data]);
	}
}
