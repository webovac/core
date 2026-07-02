<?php

declare(strict_types=1);

namespace Webovac\Core\Model;

use DateTimeInterface;


interface HasDate extends ICmsEntity
{
	function getDate(): ?DateTimeInterface;
}
