<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Page;

use App\Model\Language\LanguageData;
use App\Model\Log\Log;
use App\Model\Page\Page;
use App\Model\PageTranslation\PageTranslation;
use Nextras\Orm\Collection\ArrayCollection;
use Nextras\Orm\Collection\ICollection;
use Webovac\Core\Control\PageItem\IPageItemControl;
use Webovac\Core\Control\PageItem\PageItemControl;
use Webovac\Core\IndexDefinition;
use Webovac\Core\IndexTranslationDefinition;
use Webovac\Core\Model\LinkableTrait;


/**
 * @property string $title {virtual}
 */
trait CorePage
{
	use LinkableTrait;


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


	public function getterTitle(): ?string
	{
		if ($this->type === self::TYPE_SEPARATOR) {
			return 'Oddělovač';
		}
		return $this->getTranslation($this->dataProvider->getLanguageData())?->title ?: 'Stránka';
	}


	public function getIcon(): ?string
	{
		return $this->type === Page::TYPE_MODULE
			? $this->targetModule->icon
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
				foreach ($page->targetModule->getPages() as $modulePage) {
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


	public function getComponent(string $moduleClass, string $templateName): PageItemControl
	{
		return $this->component->create($this, $moduleClass, $templateName);
	}


	public function getParameters(): array
	{
		return $this->web
			? [$this->getPageName() => $this->name]
			: [$this->module->getPageName() => $this->module->name, $this->getPageName() => $this->name];
	}


	public function getPageName(): string
	{
		return $this->web ? 'Admin:PageDetail' : 'TemplateDetail';
	}


	public function getEntityIcon(): string
	{
		return 'file';
	}


	public function getIndexDefinition(): IndexDefinition
	{
		$definition = new IndexDefinition;
		$definition->entity = $this;
		$definition->entityName = 'page';
		foreach ($this->translations as $translation) {
			$translationDefinition = new IndexTranslationDefinition;
			$translationDefinition->language = $translation->language;
			$translationDefinition->documents = ['A' => $this->name, 'B' => $translation->title, 'C' => $translation->description];
			$definition->translations[] = $translationDefinition;
		}
		return $definition;
	}


	public function createLog(string $type): ?Log
	{
		$log = new Log;
		$log->page = $this;
		$log->type = $type;
		$log->createdByPerson = match($type) {
			Log::TYPE_CREATE => $this->createdByPerson,
			Log::TYPE_UPDATE => $this->updatedByPerson,
		};
		$log->date = match($type) {
			Log::TYPE_CREATE => $this->createdAt,
			Log::TYPE_UPDATE => $this->updatedAt,
		};
		return $log;
	}
}
