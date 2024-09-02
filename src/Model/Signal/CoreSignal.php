<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Signal;

use App\Model\Page\Page;
use App\Model\Person\Person;
use Nextras\Dbal\Utils\DateTimeImmutable;


/**
 * @property int $id {primary}
 *
 * @property string $signal
 * @property string $name
 *
 * @property DateTimeImmutable $createdAt {default now}
 * @property DateTimeImmutable|null $updatedAt
 *
 * @property Page $page {m:1 Page::$signals}
 * @property Person|null $createdByPerson {m:1 Person, oneSided=true}
 * @property Person|null $updatedByPerson {m:1 Person, oneSided=true}
 */
trait CoreSignal
{
}
