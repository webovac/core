<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Asset;


/**
 * @property string $title {virtual}
 */
trait CoreAsset
{
	public function getterTitle(): string
	{
		return $this->name;
	}
}
