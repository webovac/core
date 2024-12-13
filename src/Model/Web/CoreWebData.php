<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Web;

use App\Model\File\File;
use App\Model\File\FileData;
use App\Model\Page\PageData;
use App\Model\Web\Web;
use App\Model\WebTranslation\WebTranslationData;
use DateTimeInterface;
use Stepapo\Utils\Attribute\ArrayOfType;
use Stepapo\Utils\Attribute\DefaultValue;
use Stepapo\Utils\Attribute\Type;


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
	#[Type(FileData::class), DefaultValue(File::DEFAULT_ICON)] public ?FileData $iconFile;
	#[Type(FileData::class)] public ?FileData $largeIconFile;
	#[Type(FileData::class), DefaultValue(File::DEFAULT_ICON)] public ?FileData $logoFile;
	#[Type(FileData::class)] public ?FileData $backgroundFile;
	/** @var WebTranslationData[] */ #[ArrayOfType(WebTranslationData::class)] public array|null $translations;
	/** @var PageData[]|array */ #[ArrayOfType(PageData::class)] public array|null $pages;
	/** @var string[] */ public array|null $modules;
	#[DefaultValue('')] public string $basePath;
	#[DefaultValue(false)] public bool $isAdmin;
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
			. '/style';
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


//	public static function createFromArray(mixed $config = [], mixed $key = null, bool $skipDefaults = false, mixed $parentKey = null): static
//	{
//		foreach (['iconFile', 'logoFile', 'backgroundFile'] as $name) {
//			if (isset($config[$name]) and is_string($config[$name])) {
//				$upload = $config[$name];
//				$config[$name] = new FileData;
//				$config[$name]->upload = $upload;
//			}
//		}
//		return parent::createFromArray($config, $key, $skipDefaults, $parentKey);
//	}
}