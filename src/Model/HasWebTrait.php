<?php

declare(strict_types=1);

namespace Webovac\Core\Model;

use Build\Model\Web\Web;
use Build\Model\Web\WebData;


trait HasWebTrait
{
	public function getWeb(): Web
	{
		return $this->web;
	}


	public function checkWeb(WebData $webData): bool
	{
		return $this->getWeb()->id === $webData->id;
	}
}
