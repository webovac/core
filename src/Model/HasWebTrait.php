<?php

declare(strict_types=1);

namespace Webovac\Core\Model;

use Build\Model\Web\WebData;


trait HasWebTrait
{
	public function checkWeb(WebData $webData): bool
	{
		return $this->web->id === $webData->id;
	}
}