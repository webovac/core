<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Lib;

use App\Model\Lib\Lib;
use App\Model\Lib\LibData;


trait CoreLibRepository
{
	public function getByData(LibData|string $data): ?Lib
	{
		return $this->getBy(['name' => $data instanceof LibData ? $data->name : $data]);
	}
}
