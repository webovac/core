<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Preference;

use App\Model\Language\Language;
use App\Model\Person\Person;
use App\Model\Web\Web;
use Nextras\Dbal\Utils\DateTimeImmutable;


/**
 * @property int $id {primary}
 *
 * @property Person $person {m:1 Person::$preferences}
 * @property Web $web {m:1 Web::$preferences}
 * @property Language|null $language {m:1 Language, oneSided=true}
 *
 * @property DateTimeImmutable $createdAt {default now}
 * @property DateTimeImmutable|null $updatedAt
 */
trait CorePreference
{
}
