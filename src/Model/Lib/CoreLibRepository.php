<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Lib;

use Build\Model\Lib\Lib;
use Build\Model\Lib\LibData;


trait CoreLibRepository
{
	public function getByData(LibData|string $data): ?Lib
	{
		return $this->getBy(['name' => $data instanceof LibData ? $data->name : $data]);
	}
}
