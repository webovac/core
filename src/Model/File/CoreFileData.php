<?php

declare(strict_types=1);

namespace Webovac\Core\Model\File;

use App\Model\File\File;
use App\Model\FileTranslation\FileTranslationData;
use Nette\Http\FileUpload;
use Stepapo\Utils\Attribute\ArrayOfType;
use Stepapo\Utils\Attribute\DontCache;
use Stepapo\Utils\Attribute\ValueProperty;


trait CoreFileData
{
	public int|null $id;
	public string|null $identifier;
	public string|null $compatibleIdentifier;
	public string|null $modernIdentifier;
	public string|null $previewIdentifier;
	public string|null $name;
	public string|null $extension;
	public string|null $contentType;
	public string|null $type;
	public int|null $width;
	public int|null $height;
	#[ValueProperty] public string|FileUpload|null $upload;
	/** @var FileTranslationData[] */ #[ArrayOfType(FileTranslationData::class)] public array|null $translations;
	public bool $forceSquare = false;
	#[DontCache] public int|string|null $createdByPerson;
	#[DontCache] public ?\DateTimeInterface $createdAt;


	public function getDefaultIdentifier(): ?string
	{
		return $this->type === File::TYPE_IMAGE ? $this->modernIdentifier : $this->identifier;
	}


	public function getIconIdentifier(): ?string
	{
		if ($this->type === File::TYPE_FILE) {
			return null;
		}
		return $this->type === File::TYPE_SVG ? $this->compatibleIdentifier : $this->identifier;
	}


	public function getBackgroundIdentifier(): ?string
	{
		if ($this->type === File::TYPE_FILE) {
			return null;
		}
		return $this->modernIdentifier;
	}
}