<?php

declare(strict_types=1);

namespace Webovac\Core\Model\IndexTranslation;

use App\Model\File\File;
use App\Model\Index\Index;
use App\Model\IndexTranslation\IndexTranslation;
use App\Model\Language\Language;
use App\Model\Language\LanguageData;
use App\Model\Module\Module;
use App\Model\Page\Page;
use App\Model\PageTranslation\PageTranslation;
use App\Model\Person\Person;
use App\Model\Role\Role;
use App\Model\Web\Web;
use Nextras\Dbal\Utils\DateTimeImmutable;
use Nextras\Orm\Collection\ArrayCollection;
use Nextras\Orm\Collection\ICollection;
use Nextras\Orm\Relationships\ManyHasMany;
use Nextras\Orm\Relationships\OneHasMany;


/**
 * @property int $id {primary}
 *
 * @property string $document
 *
 * @property Index $index {m:1 Index::$translations}
 * @property Language $language {m:1 Language, oneSided=true}
 */
trait CoreIndexTranslation
{
}
