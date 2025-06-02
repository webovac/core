<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Lib;


/**
 * @property string $title {virtual}
 */
trait CoreLib
{
	public function getterTitle(): string
	{
		return $this->name;
	}
}
