<?php

declare(strict_types=1);

namespace Webovac\Core\Model;

use Build\Model\Web\WebData;


trait HasWebFilterTrait
{
	public function getWebFilter(WebData $webData): array
	{
		return ['web->id' => $webData->id];
    }


	public function shouldFilterByWeb(WebData $webData): bool
	{
		return !$webData->isAdmin && $this->getWebFilter($webData);
	}
}