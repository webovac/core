<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Web;

use App\Model\File\File;
use App\Model\File\FileData;
use App\Model\Module\ModuleData;
use App\Model\Module\ModuleRepository;
use App\Model\Page\Page;
use App\Model\Page\PageData;
use App\Model\PageTranslation\PageTranslationData;
use App\Model\Web\Web;
use App\Model\Web\WebData;
use App\Model\WebTranslation\WebTranslationData;
use DateTimeInterface;
use Nette\Http\FileUpload;
use Nette\InvalidArgumentException;
use Nette\Schema\Processor;
use ReflectionException;
use Stepapo\Utils\Attribute\ArrayOfType;
use Stepapo\Utils\Attribute\DefaultValue;
use Stepapo\Utils\Attribute\Type;
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
	/** @var WebTranslationData[] */ #[ArrayOfType(WebTranslationData::class)] public array|null $translations;
	/** @var PageData[]|array */ #[ArrayOfType(PageData::class)] public array|null $pages;
	/** @var string[] */ public array|null $modules;
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
}