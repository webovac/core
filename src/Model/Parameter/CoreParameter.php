<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Parameter;

use App\Model\Page\Page;
use App\Model\Person\Person;
use Nextras\Dbal\Utils\DateTimeImmutable;


/**
 * @property int $id {primary}
 *
 * @property string $query
 * @property string $parameter
 *
 * @property DateTimeImmutable $createdAt {default now}
 * @property DateTimeImmutable|null $updatedAt
 *
 * @property Page $page {m:1 Page::$parameters}
 * @property Person|null $createdByPerson {m:1 Person, oneSided=true}
 * @property Person|null $updatedByPerson {m:1 Person, oneSided=true}
 */
trait CoreParameter
{
}
