<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Web;

use App\Model\File\File;
use App\Model\File\FileData;
use App\Model\Module\ModuleData;
use App\Model\Page\PageData;
use App\Model\Web\Web;
use App\Model\Web\WebData;
use App\Model\WebTranslation\WebTranslationData;
use DateTimeInterface;
use Nette\Http\FileUpload;
use Nette\Schema\Processor;
use Stepapo\Utils\Attribute\ArrayOfType;
use Stepapo\Utils\Attribute\DefaultValue;
use Stepapo\Utils\Expect;


trait CoreWebData
{
	public ?int $id;
	public string $code;
	public string $host;
	public int|string|null $homePage;
	#[DefaultValue(Web::DEFAULT_COLOR)] public string $color;
	#[DefaultValue(Web::DEFAULT_COMPLEMENTARY_COLOR)] public string $complementaryColor;
	#[DefaultValue(Web::DEFAULT_ICON_BACKGROUND_COLOR)] public string $iconBackgroundColor;
	#[DefaultValue('cs')] public int|string $defaultLanguage;
	#[DefaultValue(File::DEFAULT_ICON)] public FileUpload|FileData|string|int|null $iconFile;
	public FileUpload|FileData|string|int|null $largeIconFile;
	#[DefaultValue(File::DEFAULT_ICON)] public FileUpload|FileData|string|int|null $logoFile;
	public FileUpload|FileData|string|int|null $backgroundFile;
	#[ArrayOfType(WebTranslationData::class)] /** @var WebTranslationData[] */ public array|null $translations;
	/** @var PageData[]|array */ public array|null $pages;
	/** @var WebModuleData[]|string[] */ public array|null $webModules;
	/** @var ModuleData[]|string[] */ public array|null $modules;
	#[DefaultValue('')] public string $basePath;
	public array $tree;
	public int|string|null $createdByPerson;
	public int|string|null $updatedByPerson;
	public ?DateTimeInterface $createdAt;
	public ?DateTimeInterface $updatedAt;


	public function getStyleRouteMask(): string
	{
		return '//'
			. $this->host
			. ($this->basePath ? ('/' . $this->basePath) : '')
			. '/style.css';
	}


	public function getStyleRouteMetadata(): array
	{
		return [
			'presenter' => 'Style',
			'action' => 'default',
			'host' => $this->host,
			'basePath' => $this->basePath,
		];
	}


	public function getManifestRouteMask(?string $language): string
	{
		return '//'
			. $this->host
			. ($this->basePath ? ('/' . $this->basePath) : '')
			. ($language ? ('/' . $language) : '')
			. '/manifest.json';
	}


	public function getManifestRouteMetadata(string $language): array
	{
		return [
			'presenter' => 'Manifest',
			'action' => 'default',
			'host' => $this->host,
			'basePath' => $this->basePath,
			'lang' => $language,
		];
	}


	public static function createFromArray(mixed $config = [], mixed $key = null, bool $skipDefaults = false): static
	{
		$data = parent::createFromArray($config, $key, $skipDefaults);
		$rank = 1;
		WebData::processWebModules($data);
		if (isset($data->tree)) {
			foreach ($data->tree as $parentPage => $pages) {
				if (!WebData::checkPage($parentPage, $data)) {
					continue;
				}
				WebData::processTree((array) $pages, $parentPage, $rank++, $data);
			}
		}
		if (isset($data->pages)) {
			foreach ($data->pages as $pageKey => $pageConfig) {
				if (!WebData::checkPage($pageKey, $data)) {
					unset($data->pages[$pageKey]);
					continue;
				}
				$data->pages[$pageKey] = PageData::createFromArray($pageConfig, $pageKey, $skipDefaults);
			}
		}
		if (isset($data->webModules)) {
			foreach ($data->webModules as $webModuleKey => $webModuleConfig) {
				$webModuleConfig['name'] ??= $webModuleKey;
				unset($data->webModules[$webModuleKey]);
				$data->webModules[$webModuleKey] = (new Processor)->process(Expect::fromSchematic(WebModuleData::class, $skipDefaults), $webModuleConfig);
			}
		}
		return $data;
	}


	private static function processWebModules(WebData &$config): void
	{
		if (!isset($config->webModules)) {
			return;
		}
		foreach ($config->webModules as $key => $moduleName) {
			unset($config->webModules[$key]);
//			if (!$this->moduleChecker->isModuleInstalled(lcfirst($moduleName))) {
//				continue;
//			}
			$config->webModules[$moduleName] = [];
		}
	}


	private static function checkPage(string $page, WebData $data)
	{
		if (isset($data->pages[$page])) {
			$p = $data->pages[$page];
			$relatedPage = $p['targetPage'] ?? ($p['redirectPage'] ?? null);
			if (!$relatedPage) {
				return true;
			}
			return WebData::checkPage(str_contains($relatedPage, ':') ? strtok($relatedPage, ':') : $relatedPage, $data);
		}
		if (isset($data->webModules[$page])) {
			//return $this->moduleChecker->isModuleInstalled(lcfirst($page));
			return true;
		}
		return false;
	}


	private static function processTree(array $pages, string $parentPage, int $rank, WebData &$data): void
	{
		$r = 1;
		if (isset($data->pages[$parentPage])) {
			$data->pages[$parentPage]['rank'] = $rank;
		} else {
			$data->webModules[$parentPage]['rank'] = $rank;
		}
		foreach ($pages as $page => $subPages) {
			if (!WebData::checkPage($page, $data)) {
				continue;
			}
			if (isset($data->pages[$page])) {
				$data->pages[$page]['parentPage'] = $parentPage;
			} else {
				$data->webModules[$page]['parentPage'] = $parentPage;
			}
			WebData::processTree((array) $subPages, $page, $r++, $data);
		}
	}
}