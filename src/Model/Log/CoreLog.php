<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Log;

use App\Model\Language\Language;
use App\Model\Module\Module;
use App\Model\Page\Page;
use App\Model\Person\Person;
use App\Model\Web\Web;
use Nextras\Dbal\Utils\DateTimeImmutable;


/**
 * @property int $id {primary}
 *
 * @property Language|null $createdLanguage {m:1 Language, oneSided=true}
 * @property Language|null $updatedLanguage {m:1 Language, oneSided=true}
 * @property Module|null $createdModule {m:1 Module, oneSided=true}
 * @property Module|null $updatedModule {m:1 Module, oneSided=true}
 * @property Page|null $createdPage {m:1 Page, oneSided=true}
 * @property Page|null $updatedPage {m:1 Page, oneSided=true}
 * @property Web|null $createdWeb {m:1 Web, oneSided=true}
 * @property Web|null $updatedWeb {m:1 Web, oneSided=true}
 * @property Person|null $person {m:1 Person, oneSided=true}
 *
 * @property DateTimeImmutable $date
 */
trait CoreLog
{
}
