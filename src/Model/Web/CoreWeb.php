<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Web;

use App\Model\File\File;
use App\Model\Language\Language;
use App\Model\Language\LanguageData;
use App\Model\Module\Module;
use App\Model\Page\Page;
use App\Model\Person\Person;
use App\Model\Preference\Preference;
use App\Model\WebTranslation\WebTranslation;
use Nextras\Dbal\Utils\DateTimeImmutable;
use Nextras\Orm\Collection\ArrayCollection;
use Nextras\Orm\Collection\ICollection;
use Nextras\Orm\Relationships\ManyHasMany;
use Nextras\Orm\Relationships\OneHasMany;


/**
 * @property int $id {primary}
 *
 * @property string $code
 * @property string $color
 * @property string $complementaryColor
 * @property string $iconBackgroundColor {default '#ffffff'}
 * @property string $host
 * @property string $basePath {default ''}
 *
 * @property DateTimeImmutable $createdAt {default now}
 * @property DateTimeImmutable|null $updatedAt
 * @property DateTimeImmutable $publishedAt {default now}
 *
 * @property File|null $iconFile {m:1 File, oneSided=true}
 * @property File|null $largeIconFile {m:1 File, oneSided=true}
 * @property File|null $logoFile {m:1 File, oneSided=true}
 * @property File|null $backgroundFile {m:1 File, oneSided=true}
 * @property Page|null $homePage {m:1 Page, oneSided=true}
 * @property Language|null $defaultLanguage {m:1 Language, oneSided=true}
 * @property Person|null $createdByPerson {m:1 Person, oneSided=true}
 * @property Person|null $updatedByPerson {m:1 Person, oneSided=true}
 *
 * @property OneHasMany|Page[] $pages {1:m Page::$web, orderBy=rank}
 * @property OneHasMany|WebTranslation[] $translations {1:m WebTranslation::$web, orderBy=language->id}
 * @property OneHasMany|Preference[] $preferences {1:m Preference::$web}
 *
 * @property ManyHasMany|Module[] $modules {m:m Module::$webs}
 */
trait CoreWeb
{
	public const DEFAULT_COLOR = '#2196f3';
	public const DEFAULT_COMPLEMENTARY_COLOR = '#cccccc';
	public const DEFAULT_ICON_BACKGROUND_COLOR = '#d3eafd';


	public function getTitle(LanguageData $language): string
	{
		return $this->getTranslation($language)->title;
	}


	public function getTranslation(LanguageData $language): ?WebTranslation
	{
		return $this->translations->toCollection()->getBy(['language' => $language->id]);
	}


	/** @return Page[]&ICollection */
	public function getPages(): ICollection
	{
		return $this->pages->toCollection()->findBy(['parentPage' => null]);
	}


	/** @return Page[]&ICollection */
	public function getPagesForMenu(): ICollection
	{
		$pages = [];
		/** @var Page $page */
		foreach ($this->pages->toCollection()->findBy(['parentPage' => null]) as $page) {
			if ($page->type === Page::TYPE_MODULE) {
				foreach ($page->module->getPages() as $modulePage) {
					$pages[] = $modulePage;
				}
			} else {
				$pages[] = $page;
			}
		}
		return new ArrayCollection($pages, $this->getRepository());
	}
}
