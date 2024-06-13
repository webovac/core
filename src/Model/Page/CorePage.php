<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Page;

use App\Model\File\File;
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
use Webovac\Core\Control\PageItem\IPageItemControl;
use Webovac\Core\Control\PageItem\PageItemControl;


/**
 * @property int $id {primary}
 *
 * @property string|null $icon
 * @property string $name
 * @property string $type {enum Page::TYPE_*} {default Page::TYPE_PAGE}
 * @property string $accessFor {enum Page::ACCESS_FOR_*} {default Page::ACCESS_FOR_ALL}
 * @property string|null $authorizingTag
 * @property string|null $authorizingParentTag
 * @property string|null $style {enum Page::STYLE_*}
 * @property bool $hasParameter {default false}
 * @property bool $hasParentParameter {default false}
 * @property bool $providesNavigation {default false}
 * @property bool $providesButtons {default false}
 * @property bool $hideInNavigation {default false}
 * @property bool $stretched {default false}
 * @property bool $dontInheritPath {default false}
 * @property bool $dontInheritAccessSetup {default false}
 * @property string|null $repository
 * @property string|null $parentRepository
 * @property mixed|null $targetParameter
 * @property mixed|null $targetParentParameter
 * @property string|null $targetSignal
 * @property string|null $targetUrl
 * @property int $rank
 *
 * @property DateTimeImmutable $createdAt {default now}
 * @property DateTimeImmutable|null $updatedAt
 * @property DateTimeImmutable $publishedAt {default now}
 *
 * @property Web|null $web {m:1 Web::$pages}
 * @property Module|null $module {m:1 Module::$pages}
 * @property Page|null $parentPage {m:1 Page::$childPages}
 * @property Page|null $redirectPage {m:1 Page, oneSided=true}
 * @property Page|null $templatePage {m:1 Page, oneSided=true}
 * @property Page|null $targetPage {m:1 Page, oneSided=true}
 * @property File|null $imageFile {m:1 File, oneSided=true}
 * @property Person|null $createdByPerson {m:1 Person, oneSided=true}
 * @property Person|null $updatedByPerson {m:1 Person, oneSided=true}
 *
 * @property OneHasMany|Page[] $childPages {1:m Page::$parentPage, orderBy=rank}
 * @property OneHasMany|PageTranslation[] $translations {1:m PageTranslation::$page, orderBy=language->rank}
 *
 * @property ManyHasMany|Person[] $authorizedPersons {m:m Person, isMain=true, oneSided=true}
 * @property ManyHasMany|Role[] $authorizedRoles {m:m Role, isMain=true, oneSided=true}
 */
trait CorePage
{
	public const string TYPE_PAGE = 'page';
	public const string TYPE_SIGNAL = 'signal';
	public const string TYPE_INTERNAL_LINK = 'internalLink';
	public const string TYPE_EXTERNAL_LINK = 'externalLink';
	public const string TYPE_TEXT = 'text';
	public const string TYPE_SEPARATOR = 'separator';
	public const string TYPE_MODULE = 'module';
	public const array TYPES = [
		Page::TYPE_PAGE => 'Stránka',
		Page::TYPE_SIGNAL => 'Akce',
		Page::TYPE_INTERNAL_LINK => 'Interní odkaz',
		Page::TYPE_EXTERNAL_LINK => 'Externí odkaz',
		Page::TYPE_TEXT => 'Text',
		Page::TYPE_SEPARATOR => 'Oddělovač',
		Page::TYPE_MODULE => 'Modul',
	];
	public const string ACCESS_FOR_ALL = 'all';
	public const string ACCESS_FOR_LOGGED = 'logged';
	public const string ACCESS_FOR_SPECIFIC = 'specific';
	public const string ACCESS_FOR_GUEST = 'guest';
	public const array ACCESS_FORS = [
		Page::ACCESS_FOR_ALL => 'Všechny',
		Page::ACCESS_FOR_LOGGED => 'Všechny přihlášené',
		Page::ACCESS_FOR_SPECIFIC => 'Některé přihlášené',
		Page::ACCESS_FOR_GUEST => 'Jen nepřihlášené',
	];
	public const string STYLE_PRIMARY = 'primary';
	public const string STYLE_SECONDARY = 'secondary';
	public const string STYLE_SUCCESS = 'success';
	public const string STYLE_DANGER = 'danger';
	public const string STYLE_WARNING = 'warning';
	public const string STYLE_INFO = 'info';
	public const string STYLE_LIGHT = 'light';
	public const string STYLE_DARK = 'dark';
	public const array STYLES = [
		Page::STYLE_PRIMARY => 'Primary',
		Page::STYLE_SECONDARY => 'Secondary',
		Page::STYLE_SUCCESS => 'Success',
		Page::STYLE_DANGER => 'Danger',
		Page::STYLE_WARNING => 'Warning',
		Page::STYLE_INFO => 'Info',
		Page::STYLE_LIGHT => 'Light',
		Page::STYLE_DARK => 'Dark',
	];

	private IPageItemControl $component;


	public function getTranslation(LanguageData $language): ?PageTranslation
	{
		return $this->translations->toCollection()->getBy(['language' => $language->id]);
	}


	public function getTitle(LanguageData $language): ?string
	{
		if ($this->type === self::TYPE_SEPARATOR) {
			return 'Oddělovač';
		}
		return $this->getTranslation($language)?->title ?: 'Stránka';
	}


	public function getIcon(): ?string
	{
		return $this->type === Page::TYPE_MODULE
			? $this->module->icon
			: $this->icon;
	}


	public function isHomePage(): bool
	{
		return $this->web && $this === $this->web->homePage;
	}


	/** @return Page[]&ICollection */ 
	public function getPages(): ICollection
	{
		return $this->childPages->toCollection();
	}


	/** @return ICollection<Page> */ 
	public function getPagesForMenu(): ICollection
	{
		$pages = [];
		/** @var Page $page */
		foreach ($this->childPages->toCollection() as $page) {
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


	public function getParentParameter(?LanguageData $language = null): int
	{
		return $this->web?->id ?: $this->module->id;
	}


	public function injectComponent(IPageItemControl $component): void
	{
		$this->component = $component;
	}


	public function getComponent(LanguageData $languageData, string $moduleClass, string $templateName): PageItemControl
	{
		return $this->component->create($this, $languageData, $moduleClass, $templateName);
	}
}
