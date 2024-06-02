<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Role;


use App\Model\Person\Person;
use Nextras\Orm\Relationships\ManyHasMany;

/**
 * @property int $id {primary}
 *
 * @property string $code
 *
 * @property ManyHasMany|Person[] $persons {m:m Person::$roles, isMain=true}
 */
trait CoreRole
{
}
