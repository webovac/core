<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Log;

use App\Model\Language\Language;
use App\Model\Log\Log;
use App\Model\Module\Module;
use App\Model\Page\Page;
use App\Model\Person\Person;
use App\Model\Web\Web;
use Nextras\Dbal\Utils\DateTimeImmutable;


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
