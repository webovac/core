<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Web;

use App\Model\File\File;
use App\Model\File\FileRepository;
use App\Model\Module\ModuleRepository;
use App\Model\Page\Page;
use App\Model\Page\PageRepository;
use App\Model\Person\Person;
use App\Model\Web\Web;
use App\Model\Web\WebData;
use Nette\Http\FileUpload;
use Nette\Utils\Image;
use Nette\Utils\ImageColor;
use Nette\Utils\ImageType;
use Nette\Utils\Random;
use Webovac\Core\Model\CmsDataRepository;


trait CoreWebRepository
{
	public function getByParameter(mixed $parameter): ?Web
	{
		return $this->getBy(['id' => $parameter]);
	}


	public function postProcessFromData(WebData $data, Web $web, ?Person $person = null, string $mode = CmsDataRepository::MODE_INSTALL): Web
	{
		if (isset($data->homePage)) {
			$web->homePage = $this->getModel()->getRepository(PageRepository::class)->getBy(['web' => $web, 'name' => $data->homePage]);
		}
		if (isset($data->webModules)) {
			foreach ($data->webModules as $webModuleData) {
				$module = $this->getModel()->getRepository(ModuleRepository::class)->getBy(['name' => $webModuleData->name]);
				if (!$module) {
					continue;
				}
				$this->getModel()->getRepository(PageRepository::class)->createModulePage($web, $module, null, $webModuleData->rank - 1);
				if (!$web->modules->has($module)) {
					$web->modules->add($module);
				}
			}
		}
		$this->persist($web);
		if (isset($data->pages)) {
			/** @var Page $page */
			foreach ($web->pages->toCollection()->findBy(['module' => null]) as $page) {
				if (!array_key_exists($page->name, $data->pages)) {
					if ($mode === CmsDataRepository::MODE_INSTALL) {
						$this->getModel()->getRepository(PageRepository::class)->delete($page);
					}
					continue;
				}
				$this->getModel()->getRepository(PageRepository::class)->postProcessFromData($data->pages[$page->name], $page, mode: $mode);
			}
		}
		if (isset($data->webModules)) {
			/** @var Page $page */
			foreach ($web->pages->toCollection()->findBy(['module!=' => null]) as $page) {
				if (!array_key_exists($page->module->name, $data->webModules)) {
					$this->getModel()->getRepository(PageRepository::class)->delete($page);
				}
				$this->getModel()->getRepository(PageRepository::class)->postProcessModulePageFromData($data->webModules[$page->module->name], $page);
			}
		}
		if ((isset($data->iconFile) && (($data->iconFile instanceof FileUpload && $data->iconFile->hasFile()) || is_string($data->iconFile))) || ($mode === CmsDataRepository::MODE_UPDATE && (isset($data->color) || isset($data->complementaryColor)))) {
			$web->iconFile = $this->styleFile($web->iconFile, $data->complementaryColor, $data->color, $person, true);
			$web->largeIconFile = $this->createLargeIcon($web, $data, $person);
			$this->persist($web);
		}
		if ((isset($data->logoFile) && (($data->logoFile instanceof FileUpload && $data->logoFile->hasFile()) || is_string($data->logoFile))) || ($mode === CmsDataRepository::MODE_UPDATE && (isset($data->color) || isset($data->complementaryColor)))) {
			$web->logoFile = $this->styleFile($web->logoFile, $data->complementaryColor, $data->color, $person);
			$this->persist($web);
		}
		return $web;
	}


	public function getByData(WebData $data): ?Web
	{
		return $this->getBy(['host' => $data->host, 'basePath' => $data->basePath]);
	}


	public function styleFile(File $file, string $primary, string $secondary, ?Person $person = null, bool $forceSquare = false): File
	{
		if ($file->type !== File::TYPE_SVG) {
			return $file;
		}
		$iconPath = $this->fileUploader->getPath($file->identifier);
		$content = file_get_contents($iconPath);
		$content = preg_replace_callback_array([
			'/<!--.*-->/' => fn() => "",
			'/<style>.*<\/style>/' => fn() => "<style>.fa-primary{fill:$primary}.fa-secondary{fill:$secondary}</style>",
		], $content);
		$upload = $this->getModel()->getRepository(FileRepository::class)->createFileUploadFromContent($content, $file);
		return $this->getModel()->getRepository(FileRepository::class)->createFile($upload, $person, $forceSquare);
	}


	public function createLargeIcon(Web $web, WebData $data, ?Person $person = null): File
	{
		$iconFile = Image::fromFile($this->fileUploader->getPath($web->iconFile->getIconIdentifier()));
		$width = $iconFile->getWidth();
		$height = $iconFile->getHeight();
		$largeIconFile = Image::fromBlank($width, $height, ImageColor::hex($data->iconBackgroundColor));
		$largeIconFile->resize('133%', '133%');
		$largeIconFile->place($iconFile, (int) round($width / 6), (int) round($height / 6));
		$name = Random::generate(8) . '.png';
		$path = $this->dir->getTempDir() . '/' . $name;
		$largeIconFile->save($path, type: ImageType::PNG);
		$upload = new FileUpload([
			'name' => $name,
			'full_path' => $path,
			'size' => filesize($path),
			'tmp_name' => $path,
			'error' => filesize($path) ? UPLOAD_ERR_OK : UPLOAD_ERR_NO_FILE,
		]);
		$largeIconFile = $this->getModel()->getRepository(FileRepository::class)->createFile($upload, $person);
		unlink($path);
		return $largeIconFile;
	}
}
