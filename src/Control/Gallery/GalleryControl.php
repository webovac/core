<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Gallery;

use App\Model\File\File;
use App\Model\Orm;
use Nextras\Orm\Collection\ICollection;
use ReflectionException;
use Webovac\Core\Control\BaseControl;
use Webovac\Core\Lib\DataProvider;
use Webovac\Core\Lib\FileUploader;
use Webovac\Core\Model\File\HasFiles;


/**
 * @property GalleryTemplate $template
 */
class GalleryControl extends BaseControl
{
	public function __construct(
		private ?HasFiles $hasFiles,
		private FileUploader $fileUploader,
		private DataProvider $dataProvider,
		private Orm $orm,
	) {
		$this->hasFiles = $this->hasFiles instanceof HasFiles ? $this->hasFiles : $this->orm->pageRepository->getById($this->dataProvider->getPageData()->id);
	}


	/**
	 * @throws ReflectionException
	 */
	public function render(): void
	{
		$files = $this->hasFiles->getFiles()->toCollection()->findBy(['type!=' => File::TYPE_FILE])->orderBy('createdAt', ICollection::DESC);
		$this->template->files = $files;
		$this->template->fileCount = $files->count();
		$this->template->languageData = $this->dataProvider->getLanguageData();
		$urls = [];
		foreach ($files as $item) {
			$urls[$item->id] = [
				'full' => $this->fileUploader->getUrl($item->modernIdentifier, '1920x1920', 'fit'),
				'preview' => $this->fileUploader->getUrl($item->modernIdentifier, '360x360', 'fill'),
			];
		}
		$this->template->urls = $urls;
		$this->template->render(__DIR__ . '/gallery.latte');
	}
}
