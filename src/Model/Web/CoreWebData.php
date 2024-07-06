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
use Webovac\Core\Model\CmsDataRepository;


trait CoreWebData
{
	public ?int $id;
	public string $code;
	public string $host;
	public int|string|null $homePage;
	#[DefaultValue(Web::DEFAULT_COLOR)] public string $color;
	#[DefaultValue(Web::DEFAULT_COMPLEMENTARY_COLOR)] public string $complementaryColor;
	#[DefaultValue(Web::DEFAULT_ICON_BACKGROUND_COLOR)] public string $iconBackgroundColor;
	#[DefaultValue('cs')] public int|string $defaultLanguage = 'cs';
	#[DefaultValue(File::DEFAULT_ICON)] public FileUpload|FileData|string|int|null $iconFile;
	public FileUpload|FileData|string|int|null $largeIconFile;
	#[DefaultValue(File::DEFAULT_ICON)] public FileUpload|FileData|string|int|null $logoFile;
	public FileUpload|FileData|string|int|null $backgroundFile;
	#[ArrayOfType(WebTranslationData::class, 'language')] /** @var WebTranslationData[] */ public array $translations;
	/** @var PageData[]|array */ public array $pages;
	/** @var WebModuleData[]|string[] */ public array $webModules;
	/** @var ModuleData[]|string[] */ public array $modules;
	public string $basePath;
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


	public static function createFromArray(array $config, string $mode = CmsDataRepository::MODE_INSTALL): static
	{
		$data = parent::createFromArray($config, $mode);
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
			foreach ($data->pages as $key => $pageConfig) {
				if (!WebData::checkPage($key, $data)) {
					unset($data->pages[$key]);
					continue;
				}
				$pageConfig['name'] ??= $key;
				unset($data->pages[$key]);
				$data->pages[$pageConfig['name']] = PageData::createFromArray($pageConfig, $mode);
			}
		}
		if (isset($data->webModules)) {
			foreach ($data->webModules as $key => $webModuleConfig) {
				$webModuleConfig['name'] ??= $key;
				unset($data->webModules[$key]);
				$data->webModules[$webModuleConfig['name']] = (new Processor)->process(Expect::fromSchematic(WebModuleData::class, $mode), $webModuleConfig);
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