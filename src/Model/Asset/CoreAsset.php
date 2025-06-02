<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Asset;


/**
 * @property string $title {virtual}
 */
trait CoreAsset
{
	public const string TYPE_STYLESHEET = 'stylesheet';
	public const string TYPE_SCRIPT = 'script';


	public function getterTitle(): string
	{
		return $this->name;
	}
}
