<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Signal;

use App\Model\Page\Page;
use App\Model\Signal\Signal;
use App\Model\Signal\SignalData;


trait CoreSignalRepository
{
	public function getByData(SignalData|string $data, Page $page): ?Signal
	{
		return $this->getBy(['page' => $page, 'name' => $data instanceof SignalData ? $data->name : $data]);
	}
}
