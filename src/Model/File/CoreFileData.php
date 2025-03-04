<?php

declare(strict_types=1);

namespace Webovac\Core\Model\File;

use App\Model\File\File;
use Nette\Http\FileUpload;
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
	#[ValueProperty] public string|FileUpload|null $upload;
	public bool $forceSquare = false;


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