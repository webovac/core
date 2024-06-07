<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Module;

use App\Model\Language\LanguageData;
use App\Model\ModuleTranslation\ModuleTranslation;
use App\Model\Page\Page;
use App\Model\Person\Person;
use App\Model\Web\Web;
use Nextras\Dbal\Utils\DateTimeImmutable;
use Nextras\Orm\Collection\ICollection;
use Nextras\Orm\Relationships\ManyHasMany;
use Nextras\Orm\Relationships\OneHasMany;


/**
 * @property int $id {primary}
 *
 * @property string $name
 * @property string $icon
 *
 * @property DateTimeImmutable $createdAt {default now}
 * @property DateTimeImmutable|null $updatedAt
 *
 * @property Page|null $homePage {m:1 Page, oneSided=true}
 * @property Person|null $createdByPerson {m:1 Person, oneSided=true}
 * @property Person|null $updatedByPerson {m:1 Person, oneSided=true}
 *
 * @property OneHasMany|Page[] $pages {1:m Page::$module}
 * @property OneHasMany|ModuleTranslation[] $translations {1:m ModuleTranslation::$module, orderBy=language->rank}
 *
 * @property ManyHasMany|Web[] $webs {m:m Web::$modules, isMain=true}
 */
trait CoreModule
{
	public function getTranslation(LanguageData $language): ?ModuleTranslation
	{
		return $this->translations->toCollection()->getBy(['language' => $language->id]);
	}


	/** @return ICollection<Page> */ 
	public function getPages(): ICollection
	{
		return $this->pages->toCollection()->findBy(['parentPage' => null, 'web' => null]);
	}


	/** @return ICollection<Page> */ 
	public function getPagesForMenu(): ICollection
	{
		return $this->getPages();
	}


	public function getTitle(LanguageData $language): string
	{
		return $this->getTranslation($language)->title;
	}


	public function getDescription(LanguageData $language): string
	{
		return $this->getTranslation($language)->description;
	}
}
