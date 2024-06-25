<?php

declare(strict_types=1);

namespace Webovac\Core\Model\TextTranslation;

use App\Model\Language\Language;
use App\Model\Person\Person;
use App\Model\Text\Text;
use Nextras\Dbal\Utils\DateTimeImmutable;


/**
 * @property int $id {primary}
 *
 * @property string $string
 *
 * @property DateTimeImmutable $createdAt {default now}
 * @property DateTimeImmutable|null $updatedAt
 *
 * @property Text $text {m:1 Text::$translations}
 * @property Language $language {m:1 Language, oneSided=true}
 * @property Person|null $createdByPerson {m:1 Person, oneSided=true}
 * @property Person|null $updatedByPerson {m:1 Person, oneSided=true}
 */
trait CoreTextTranslation
{
}
