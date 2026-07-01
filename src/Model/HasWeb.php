<?php

declare(strict_types=1);

namespace Webovac\Core\Model;

use Build\Model\Web\Web;
use Build\Model\Web\WebData;


interface HasWeb extends ICmsEntity
{
	function getWeb(): Web;
	function checkWeb(WebData $webData): bool;
}
