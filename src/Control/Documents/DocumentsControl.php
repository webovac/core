<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Documents;

use App\Model\File\File;
use App\Model\Orm;
use Nette\Application\UI\Multiplier;
use Nextras\Orm\Collection\ICollection;
use ReflectionException;
use Webovac\Admin\Control\FileItem\FileItemControl;
use Webovac\Admin\Control\FileItem\IFileItemControl;
use Webovac\Core\Control\BaseControl;
use Webovac\Core\Lib\DataProvider;
use Webovac\Core\Model\File\HasFiles;


/**
 * @property DocumentsTemplate $template
 */
class DocumentsControl extends BaseControl
{
	public function __construct(
		private ?HasFiles $hasFiles,
		private DataProvider $dataProvider,
		private IFileItemControl $fileItem,
		private Orm $orm,
	) {
		$this->hasFiles = $this->hasFiles instanceof HasFiles
			? $this->hasFiles
			: $this->orm->pageRepository->getById($this->dataProvider->getPageData()->id);
	}


	/**
	 * @throws ReflectionException
	 */
	public function render(): void
	{
		$files = $this->hasFiles->getFiles()->toCollection()->findBy(['type' => File::TYPE_FILE])->orderBy('createdAt', ICollection::DESC);
		$this->template->files = $files->fetchPairs('id');
		$this->template->fileCount = $files->count();
		$this->template->languageData = $this->dataProvider->getLanguageData();
		$this->template->render(__DIR__ . '/documents.latte');
	}


	public function createComponentFileItem(): Multiplier
	{
		return new Multiplier(function ($id): FileItemControl {
			return $this->fileItem->create(
				$this->template->files[$id] ?? $this->orm->fileRepository->getById($id)
			);
		});
	}
}
