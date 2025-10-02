<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Log;

use Build\Model\Log\Log;


/**
 * @property string $typeLabel {virtual}
 */
trait CoreLog
{
	public const string TYPE_CREATE = 'created';
	public const string TYPE_UPDATE = 'updated';

	public const array TYPES = [
		Log::TYPE_CREATE => 'Vytvořeno',
		Log::TYPE_UPDATE => 'Upraveno',
	];


	public function getterTypeLabel(): string
	{
		return self::TYPES[$this->type];
	}
}
