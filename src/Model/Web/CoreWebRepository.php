<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Web;

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
use Nette\Http\FileUpload;
use Nette\InvalidArgumentException;
use Nette\Utils\Image;
use Nette\Utils\ImageColor;
use Nette\Utils\ImageException;
use Nette\Utils\ImageType;
use Nette\Utils\UnknownImageFileException;
use Stepapo\Model\Data\Item;
use Stepapo\Model\Orm\StepapoEntity;


trait CoreWebRepository
{
	/**
	 * @throws \ReflectionException
	 */
	public function createFromDataReturnBool(
		Item $data,
		?StepapoEntity $original = null,
		?StepapoEntity $parent = null,
		?string $parentName = null,
		?Person $person = null,
		?\DateTimeInterface $date = null,
		bool $skipDefaults = false,
		bool $getOriginalByData = false,
	): bool
	{
		if (isset($data->iconFile) || ($skipDefaults && (isset($data->color) || isset($data->complementaryColor)))) {
			$data->iconFile->upload = $this->styleFile($data->iconFile, $data->complementaryColor, $data->color);
			$data->iconFile->forceSquare = true;
			$data->largeIconFile = $data->largeIconFile ?: new FileData;
			$data->largeIconFile->upload = $this->createLargeIcon($data->iconFile, $data->iconBackgroundColor);
		}
		if (isset($data->logoFile) || ($skipDefaults && (isset($data->color) || isset($data->complementaryColor)))) {
			$data->logoFile = $data->logoFile ?: new FileData;
			$data->logoFile->upload = $this->styleFile($data->logoFile, $data->complementaryColor, $data->color);
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
		?StepapoEntity $original = null,
		?StepapoEntity $parent = null,
		?string $parentName = null,
		?Person $person = null,
		?\DateTimeInterface $date = null,
		bool $skipDefaults = false,
		bool $getOriginalByData = false,
	): StepapoEntity
	{
		if (isset($data->iconFile) || ($skipDefaults && (isset($data->color) || isset($data->complementaryColor)))) {
			$data->iconFile->upload = $this->styleFile($data->iconFile, $data->complementaryColor, $data->color);
			$data->iconFile->forceSquare = true;
			$data->largeIconFile = $data->largeIconFile ?? new FileData;
			$data->largeIconFile->upload = $this->createLargeIcon($data->iconFile, $data->iconBackgroundColor);
		}
		if (isset($data->logoFile) || ($skipDefaults && (isset($data->color) || isset($data->complementaryColor)))) {
			$data->logoFile = $data->logoFile ?? new FileData;
			$data->logoFile->upload = $this->styleFile($data->logoFile, $data->complementaryColor, $data->color);
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
		$page->icon = $module->icon;
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


	public function styleFile(FileData $file, string $primary, string $secondary): FileUpload
	{
		if (isset($file->upload)) {
			if ($file->upload instanceof FileUpload) {
				$content = file_get_contents($file->upload->getTemporaryFile());
			} else {
				$content = base64_decode($file->upload);
			}
		} else {
			$content = file_get_contents($this->fileUploader->getPath($file->identifier));
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
	public function createLargeIcon(FileData $iconFile, string $iconBackgroundColor): FileUpload
	{
		if (isset($iconFile->upload)) {
			if ($iconFile->upload instanceof FileUpload) {
				if ($iconFile->upload->getContentType() === 'image/svg+xml') {
					$image = Image::fromFile($this->getModel()->getRepository(FileRepository::class)->svg2png($iconFile->upload, true)->getTemporaryFile());
				} elseif ($iconFile->upload->getContentType() === 'image/webp' || $iconFile->upload->getContentType() === 'image/avif') {
					$image = Image::fromFile($this->getModel()->getRepository(FileRepository::class)->image2jpeg($iconFile->upload, true)->getTemporaryFile());
				} elseif (!$iconFile->upload->isImage()) {
					throw new InvalidArgumentException;
				} else {
					$image = $iconFile->upload->toImage();
				}
			} else {
				$image = Image::fromString(base64_decode($iconFile));
			}
		} else {
			$image = Image::fromString(file_get_contents($this->fileUploader->getPath($iconFile->identifier)));
		}
//		if ($iconFile->upload instanceof FileUpload) {
//			if ($iconFile->upload->getContentType() === 'image/svg+xml') {
//				$image = Image::fromFile($this->getModel()->getRepository(FileRepository::class)->svg2png($iconFile->upload, true)->getTemporaryFile());
//			} elseif ($iconFile->upload->getContentType() === 'image/webp' || $iconFile->getContentType() === 'image/avif') {
//				$image = Image::fromFile($this->getModel()->getRepository(FileRepository::class)->image2jpeg($iconFile->upload, true)->getTemporaryFile());
//			} elseif (!$iconFile->upload->isImage()) {
//				throw new InvalidArgumentException;
//			} else {
//				$image = $iconFile->upload->toImage();
//			}
//		} elseif ($iconFile instanceof File) {
//			$image = Image::fromString(file_get_contents($this->fileUploader->getPath($iconFile->identifier)));
//		} else {
//			$image = Image::fromString(base64_decode($iconFile));
//		}
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
