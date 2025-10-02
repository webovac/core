<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Text;

use Build\Model\Text\Text;
use Build\Model\Text\TextData;


trait CoreTextRepository
{
	public function getByData(TextData|string $data): ?Text
	{
		return $this->getBy(['name' => $data instanceof TextData ? $data->name : $data]);
	}
}
