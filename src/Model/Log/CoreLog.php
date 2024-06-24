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
 * @property int $id {primary}
 *
 * @property string $type {enum self::TYPE_*}
 * @property string $typeLabel {virtual}
 *
 * @property Language|null $language {m:1 Language, oneSided=true}
 * @property Module|null $module {m:1 Module, oneSided=true}
 * @property Page|null $page {m:1 Page, oneSided=true}
 * @property Web|null $web {m:1 Web, oneSided=true}
 * @property Person|null $createdByPerson {m:1 Person, oneSided=true}
 *
 * @property DateTimeImmutable $date
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
