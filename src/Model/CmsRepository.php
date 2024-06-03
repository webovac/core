<?php

namespace Webovac\Core\Model;

use App\Lib\OrmFunctions;
use App\Model\File\File;
use App\Model\File\FileData;
use App\Model\File\FileRepository;
use App\Model\Person\Person;
use Choowx\RasterizeSvg\Svg;
use Nette\DI\Attributes\Inject;
use Nette\Http\FileUpload;
use Nette\Utils\Image;
use Nette\Utils\ImageType;
use Nette\Utils\Random;
use Nextras\Orm\Entity\IEntity;
use Nextras\Orm\Entity\ToArrayConverter;
use Nextras\Orm\Relationships\ManyHasMany;
use Nextras\Orm\Relationships\ManyHasOne;
use Nextras\Orm\Relationships\OneHasMany;
use Nextras\Orm\Relationships\OneHasOne;
use Nextras\Orm\Repository\Repository;
use Webovac\Core\Lib\Dir;
use Webovac\Core\Lib\FileUploader;


abstract class CmsRepository extends Repository
{
	#[Inject] public FileUploader $fileUploader;
	#[Inject] public Dir $dir;


	public function createCollectionFunction(string $name)
	{
		if (isset(OrmFunctions::CUSTOM_FUNCTIONS[$name])) {
			return OrmFunctions::call($name);
		} else {
			return parent::createCollectionFunction($name);
		}
	}


	public function getByParameter(mixed $parameter)
	{
		return $this->getBy(['id' => $parameter]);
	}


	public function delete(IEntity $entity)
	{
		$this->mapper->delete($entity);
	}


	public function createFromData(
		CmsData $data,
		?IEntity $original = null,
		?IEntity $parent = null,
		?string $parentName = null,
		?Person $person = null,
		?\DateTimeInterface $date = null,
		string $mode = CmsDataRepository::MODE_INSTALL,
		bool $getOriginalByData = false,
	): IEntity
	{
		if ($getOriginalByData) {
			$original ??= method_exists($this, 'getByData') ? $this->getByData($data, $parent) : null;
		}
		$old = $original?->toArray(ToArrayConverter::RELATIONSHIP_AS_ID);
		$class = new \ReflectionClass($this->getEntityClassName([]));
		$entity = $original ?: $class->newInstance();
		$metadata = $entity->getMetadata();
		foreach ($data as $name => $value) {
			if (in_array($name, ['createdAt', 'updatedAt', 'createdByPerson', 'updatedByPerson'], true)) {
				continue;
			}
			$property = $metadata->hasProperty($name) ? $metadata->getProperty($name) : null;
			if (!$property || $property->isPrimary) {
				continue;
			} elseif (!$property->wrapper) {
				$entity->$name = $data->$name;
			} elseif (in_array($property->wrapper, [OneHasOne::class, ManyHasOne::class])) {
				$relatedRepository = $this->getModel()->getRepository($property->relationship->repository);
				if (isset($property->types[File::class])) {
					$entity->$name = $data->$name instanceof FileData ? $relatedRepository->getById($data->id) : ($this->getModel()->getRepository(FileRepository::class)->createFile($data->$name, $person, $name === 'iconFile') ?: $entity->$name);
				} elseif (is_numeric($data->$name)) {
					$entity->$name = $data->$name ? $relatedRepository->getById($data->$name) : null;
				} elseif (method_exists($relatedRepository, 'getByData')) {
					$entity->$name = $data->$name ? $relatedRepository->getByData($data->$name, $entity) : null;
				}
			} elseif ($property->wrapper === ManyHasMany::class) {
				$relatedRepository = $this->getModel()->getRepository($property->relationship->repository);
				$array = [];
				foreach ($data->$name as $item) {
					if (isset($property->types[File::class])) {
						$array[] = $this->getModel()->getRepository(FileRepository::class)->createFile($item, $person);
					} elseif (is_numeric($item)) {
						if ($item = $relatedRepository->getById($item)) {
							$array[] = $item;
						}
					} elseif (method_exists($relatedRepository, 'getByData')) {
						if ($item = $relatedRepository->getByData($item, $entity)) {
							$array[] = $item;
						}
					}
				}
				$entity->$name->set($array);
			}
		}
		if ($parent && $parentName) {
			$entity->$parentName = $parent;
		}
		if (!$original) {
			if ($metadata->hasProperty('createdByPerson')) {
				$entity->createdByPerson = $person;
			}
			$this->persist($entity);
		} elseif ($entity->isChanged($old)) {
			if ($metadata->hasProperty('updatedByPerson')) {
				$entity->updatedByPerson = $person;
			}
			if ($metadata->hasProperty('updatedAt')) {
				$entity->updatedAt = $date;
			}
			$this->persist($entity);
		}

		foreach ($data as $name => $value) {
			if (!isset($data)) {
				continue;
			}
			$property = $metadata->hasProperty($name) ? $metadata->getProperty($name) : null;
			if (!$property) {
				continue;
			} elseif ($property->wrapper === OneHasMany::class) {
				$ids = [];
				$relatedRepository = $this->getModel()->getRepository($property->relationship->repository);
				foreach ($data->$name as $relatedData) {
					$original = method_exists($relatedRepository, 'getByData') ? $relatedRepository->getByData($relatedData, $entity) : null;
					$related = $relatedRepository->createFromData($relatedData, $original, $entity, $property->relationship->property, person: $person, date: $date);
					$ids[] = $related->getPersistedId();
				}
				/** Promazat zrušené entity */
				if ($original && $mode === CmsDataRepository::MODE_INSTALL) {
					foreach ($entity->$name as $related) {
						if (!in_array($related->getPersistedId(), $ids, true)) {
							$relatedRepository->delete($related);
						}
					}
				}
			}
		}
		return $entity;
	}


	public function createFile(FileUpload|string|null $upload, ?Person $person = null): ?File
	{
		if (!$upload) {
			return null;
		}
		if (is_string($upload)) {
			$upload = $this->createFileUploadFromString($upload);
		}
		if (!$upload->hasFile()) {
			return null;
		}
		$identifier = $this->fileUploader->upload($upload);
		$exists = $this->getModel()->getRepository(FileRepository::class)->getBy(['identifier' => $identifier]);
		$file = $exists ?: new File;
		$file->name = $upload->getSanitizedName();
		$file->extension = $upload->getSuggestedExtension();
		if (!$exists) {
			$file->identifier = $identifier;
			$file->contentType = $upload->getContentType();
			$file->type = $upload->getContentType() === 'image/svg+xml' ? File::TYPE_SVG : ($upload->isImage() ? File::TYPE_IMAGE : File::TYPE_FILE);
			if ($upload->getContentType() === 'image/svg+xml') {
				$compatibleUpload = $this->svg2png($upload);
				$file->compatibleIdentifier = $this->fileUploader->upload($compatibleUpload);
				$modernUpload = $this->image2webp($compatibleUpload);
				$file->modernIdentifier = $this->fileUploader->upload($modernUpload);
			} elseif ($upload->getContentType() === 'image/webp' || $upload->getContentType() === 'image/avif') {
				$compatibleUpload = $this->image2jpeg($upload);
				$file->compatibleIdentifier = $this->fileUploader->upload($compatibleUpload);
				$file->modernIdentifier = $identifier;
			} elseif ($upload->isImage()) {
				$modernUpload = $this->image2webp($upload);
				$file->compatibleIdentifier = $identifier;
				$file->modernIdentifier = $this->fileUploader->upload($modernUpload);
			}
		} else {
			$file->createdByPerson = $person;
		}
		$this->getModel()->persist($file);
		return $file;
	}


	private function svg2png(FileUpload $upload): FileUpload
	{
		$tmpFile = $upload->getTemporaryFile();
		Svg::make(file_get_contents($tmpFile))->saveAsPng($tmpFile);
		return $this->createFileUpload($upload);
	}


	private function image2webp(FileUpload $upload): FileUpload
	{
		$tmpFile = $upload->getTemporaryFile();
		Image::fromFile($tmpFile)->save($tmpFile, type: ImageType::WEBP);
		return $this->createFileUpload($upload);
	}


	private function image2jpeg(FileUpload $upload): FileUpload
	{
		$tmpFile = $upload->getTemporaryFile();
		Image::fromFile($tmpFile)->save($tmpFile, type: ImageType::JPEG);
		return $this->createFileUpload($upload);
	}


	public function createFileUpload(FileUpload $upload): FileUpload
	{
		return new FileUpload([
			'name' => $upload->getSanitizedName(),
			'full_path' => $upload->getUntrustedFullPath(),
			'size' => $upload->getSize(),
			'tmp_name' => $upload->getTemporaryFile(),
			'error' => $upload->getError(),
		]);
	}


	public function createFileUploadFromString(string $upload): FileUpload
	{
		$name = Random::generate(8);
		$path = $this->dir->getTempDir() . '/' . $name;
		$size = file_put_contents($path, base64_decode($upload));
		return new FileUpload([
			'name' => $name,
			'full_path' => $path,
			'size' => $size ?: 0,
			'tmp_name' => $path,
			'error' => $size ? UPLOAD_ERR_OK : UPLOAD_ERR_NO_FILE,
		]);
	}
}
