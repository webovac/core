<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Index;

use App\Model\File\File;
use App\Model\IndexTranslation\IndexTranslation;
use App\Model\Language\Language;
use App\Model\Language\LanguageData;
use App\Model\Module\Module;
use App\Model\Page\Page;
use App\Model\PageTranslation\PageTranslation;
use App\Model\Person\Person;
use App\Model\Role\Role;
use App\Model\Web\Web;
use Nette\InvalidStateException;
use Nextras\Dbal\Utils\DateTimeImmutable;
use Nextras\Orm\Collection\ArrayCollection;
use Nextras\Orm\Collection\ICollection;
use Nextras\Orm\Entity\IEntity;
use Nextras\Orm\Relationships\ManyHasMany;
use Nextras\Orm\Relationships\ManyHasOne;
use Nextras\Orm\Relationships\OneHasMany;
use Nextras\Orm\Relationships\OneHasOne;


/**
 * @property int $id {primary}
 *
 * @property Language|null $language {m:1 Language, oneSided=true}
 * @property Module|null $module {m:1 Module, oneSided=true}
 * @property Page|null $page {m:1 Page, oneSided=true}
 * @property Web|null $web {m:1 Web, oneSided=true}
 *
 * @property OneHasMany|IndexTranslation[] $translations {1:m IndexTranslation::$index, orderBy=language->rank}
 */
trait CoreIndex
{
	public function getEntity(): IEntity
	{
		foreach ($this->getMetadata()->getProperties() as $property) {
			$name = $property->name;
			if (in_array($property->wrapper, [OneHasOne::class, ManyHasOne::class]) && $this->$name) {
				return $this->$name;
			}
		}
		throw new InvalidStateException;
	}
}
