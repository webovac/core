<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Command;

use Build\Model\Command\Command;
use Build\Model\Command\CommandData;


trait CoreCommandRepository
{
	public function getByData(CommandData|string $data): ?Command
	{
		return $this->getBy(['code' => $data instanceof CommandData ? $data->code : $data]);
	}
}
