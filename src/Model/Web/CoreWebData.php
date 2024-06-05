<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Web;

use App\Model\File\File;
use App\Model\File\FileData;
use App\Model\Page\PageData;
use App\Model\Web\Web;
use App\Model\WebTranslation\WebTranslationData;
use DateTimeInterface;
use Nette\Http\FileUpload;
use Webovac\Core\Attribute\DefaultValue;


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
	/** @var array<WebTranslationData|array> */ public array $translations;
	/** @var array<PageData|array> */ public array $pages;
	/** @var array<WebModuleData|string> */ public array $webModules;
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
}