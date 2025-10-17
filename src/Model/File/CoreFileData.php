<?php

declare(strict_types=1);

namespace Webovac\Core\Model\File;

use Build\Model\File\File;
use Nette\Http\FileUpload;
use Stepapo\Utils\Attribute\ValueProperty;


trait CoreFileData
{
	public bool $forceSquare = false;
	#[ValueProperty] public string|FileUpload|null $upload;


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