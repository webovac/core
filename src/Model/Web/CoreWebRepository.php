<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Web;

use App\Model\File\File;
use App\Model\File\FileData;
use App\Model\File\FileRepository;
use App\Model\Module\ModuleRepository;
use App\Model\Page\Page;
use App\Model\Page\PageData;
use App\Model\Page\PageRepository;
use App\Model\PageTranslation\PageTranslationData;
use App\Model\Person\Person;
use App\Model\Web\Web;
use App\Model\Web\WebData;
use Choowx\RasterizeSvg\Svg;
use Nette\Http\FileUpload;
use Nette\InvalidArgumentException;
use Nette\Utils\Image;
use Nette\Utils\ImageColor;
use Nette\Utils\ImageException;
use Nette\Utils\ImageType;
use Nette\Utils\Random;
use Nette\Utils\UnknownImageFileException;
use Stepapo\Utils\Model\Item;
use Tracy\Dumper;
use Webovac\Core\CmsEntityProcessor;
use Webovac\Core\Lib\FileUploader;
use Webovac\Core\Model\CmsEntity;


trait CoreWebRepository
{
	/**
	 * @throws \ReflectionException
	 */
	public function createFromDataReturnBool(
		Item $data,
		?CmsEntity $original = null,
		?CmsEntity $parent = null,
		?string $parentName = null,
		?Person $person = null,
		?\DateTimeInterface $date = null,
		bool $skipDefaults = false,
		bool $getOriginalByData = false,
	): bool
	{
		if ((isset($data->iconFile) && (($data->iconFile instanceof FileUpload && $data->iconFile->hasFile()) || is_string($data->iconFile))) || ($skipDefaults && (isset($data->color) || isset($data->complementaryColor)))) {
			$data->iconFile = $this->styleFile($data->iconFile, $data->complementaryColor, $data->color);
			$data->largeIconFile = $this->createLargeIcon($data->iconFile, $data->iconBackgroundColor);
		}
		if ((isset($data->logoFile) && (($data->logoFile instanceof FileUpload && $data->logoFile->hasFile()) || is_string($data->logoFile))) || ($skipDefaults && (isset($data->color) || isset($data->complementaryColor)))) {
			$data->logoFile = $this->styleFile($data->logoFile, $data->complementaryColor, $data->color);
		}
		if (isset($data->modules)) {
			foreach ($data->modules as $moduleName) {
				$data->pages[$moduleName . 'Module'] = $this->createModulePage($moduleName);
			}
		}
		if (isset($data->tree)) {
			$rank = 1;
			foreach ($data->tree as $parentPage => $pages) {
				$this->processTree((array) $pages, $parentPage, $rank++, $data);
			}
		}
		return parent::createFromDataReturnBool($data, $original, $parent, $parentName, $person, $date, $skipDefaults, $getOriginalByData);
	}


	/**
	 * @throws \ReflectionException
	 */
	public function createFromData(
		Item $data,
		?CmsEntity $original = null,
		?CmsEntity $parent = null,
		?string $parentName = null,
		?Person $person = null,
		?\DateTimeInterface $date = null,
		bool $skipDefaults = false,
		bool $getOriginalByData = false,
	): CmsEntity
	{
		if ((isset($data->iconFile) && (($data->iconFile instanceof FileUpload && $data->iconFile->hasFile()) || is_string($data->iconFile))) || ($skipDefaults && (isset($data->color) || isset($data->complementaryColor)))) {
			$data->iconFile = $this->styleFile($data->iconFile, $data->complementaryColor, $data->color);
			$data->largeIconFile = $this->createLargeIcon($data->iconFile, $data->iconBackgroundColor);
		}
		if ((isset($data->logoFile) && (($data->logoFile instanceof FileUpload && $data->logoFile->hasFile()) || is_string($data->logoFile))) || ($skipDefaults && (isset($data->color) || isset($data->complementaryColor)))) {
			$data->logoFile = $this->styleFile($data->logoFile, $data->complementaryColor, $data->color);
		}
		if (isset($data->modules)) {
			foreach ($data->modules as $moduleName) {
				$data->pages[$moduleName . 'Module'] = $this->createModulePage($moduleName);
			}
		}
		if (isset($data->tree)) {
			$rank = 1;
			foreach ($data->tree as $parentPage => $pages) {
				$this->processTree((array) $pages, $parentPage, $rank++, $data);
			}
		}
		return parent::createFromData($data, $original, $parent, $parentName, $person, $date, $skipDefaults, $getOriginalByData);
	}


	public function createModulePage(string $moduleName): PageData
	{
		$module = $this->getModel()->getRepository(ModuleRepository::class)->getBy(['name' => $moduleName]);
		$page = new PageData;
		$page->name = $module->name . 'Module';
		$page->targetModule = $module->name;
		$page->type = Page::TYPE_MODULE;
		foreach ($module->translations as $translation) {
			$pageTranslation = new PageTranslationData;
			$pageTranslation->path = $translation->basePath;
			$pageTranslation->title = $translation->title;
			$pageTranslation->language = $translation->language->shortcut;
			$page->translations[$translation->language->shortcut] = $pageTranslation;
		}
		return $page;
	}


	private function processTree(array $pages, string $parentPage, int $rank, WebData &$data): void
	{
		$r = 1;
		if (isset($data->pages[$parentPage])) {
			$data->pages[$parentPage]->rank = $rank;
		}
		foreach ($pages as $page => $subPages) {
			if (isset($data->pages[$page])) {
				$data->pages[$page]->parentPage = $parentPage;
			} else {
				throw new InvalidArgumentException("Page '$page' not found in config.");
			}
			$this->processTree((array) $subPages, $page, $r++, $data);
		}
	}


	public function postProcessFromData(WebData $data, Web $web, ?Person $person = null, bool $skipDefaults = false): Web
	{
		if (isset($data->homePage)) {
			$web->homePage = $this->getModel()->getRepository(PageRepository::class)->getBy(['web' => $web, 'name' => $data->homePage]);
		}
		$this->persist($web);
		if (isset($data->pages)) {
			/** @var Page $page */
			foreach ($web->pages->toCollection() as $page) {
				if (!array_key_exists($page->name, $data->pages)) {
					if (!$skipDefaults) {
						$this->getModel()->getRepository(PageRepository::class)->delete($page);
					}
					continue;
				}
				$this->getModel()->getRepository(PageRepository::class)->postProcessFromData($data->pages[$page->name], $page, skipDefaults: $skipDefaults);
			}
		}
		return $web;
	}


	public function getByData(WebData $data): ?Web
	{
		return $this->getBy(['host' => $data->host, 'basePath' => $data->basePath]);
	}


	public function styleFile(File|FileUpload|string $file, string $primary, string $secondary): FileUpload
	{
		if ($file instanceof File) {
			$content = file_get_contents($this->fileUploader->getPath($file->identifier));
		} elseif ($file instanceof FileUpload) {
			$content = file_get_contents($file->getTemporaryFile());
		} else {
			$content = base64_decode($file);
		}
		return $this->getModel()->getRepository(FileRepository::class)->createFileUploadFromString(base64_encode(preg_replace_callback_array([
			'/<!--.*-->/' => fn() => "",
			'/<style>.*<\/style>/' => fn() => "<style>.fa-primary{fill:$primary}.fa-secondary{fill:$secondary}</style>",
		], $content)));
	}


	/**
	 * @throws ImageException
	 * @throws UnknownImageFileException
	 */
	public function createLargeIcon(FileUpload|File|string $iconFile, string $iconBackgroundColor): FileUpload
	{
		if ($iconFile instanceof FileUpload) {
			if ($iconFile->getContentType() === 'image/svg+xml') {
				$image = Image::fromFile($this->getModel()->getRepository(FileRepository::class)->svg2png($iconFile, true)->getTemporaryFile());
			} elseif ($iconFile->getContentType() === 'image/webp' || $iconFile->getContentType() === 'image/avif') {
				$image = Image::fromFile($this->getModel()->getRepository(FileRepository::class)->image2jpeg($iconFile, true)->getTemporaryFile());
			} elseif (!$iconFile->isImage()) {
				throw new InvalidArgumentException;
			}
		} elseif ($iconFile instanceof File) {
			$image = Image::fromString(file_get_contents($this->fileUploader->getPath($iconFile->identifier)));
		} else {
			$image = Image::fromString(base64_decode($iconFile));
		}
		$width = $image->getWidth();
		$height = $image->getHeight();
		$largeIconFile = Image::fromBlank($width, $height, ImageColor::hex($iconBackgroundColor));
		$largeIconFile->resize('133%', '133%');
		$largeIconFile->place($image, (int) round($width / 6), (int) round($height / 6));
		$name = substr(sha1($largeIconFile->toString()), 0, 8);
		$path = $this->dir->getTempDir() . '/' . $name;
		$largeIconFile->save($path, type: ImageType::PNG);
		return new FileUpload([
			'name' => $name,
			'full_path' => $path,
			'size' => filesize($path),
			'tmp_name' => $path,
			'error' => filesize($path) ? UPLOAD_ERR_OK : UPLOAD_ERR_NO_FILE,
		]);
	}
}
