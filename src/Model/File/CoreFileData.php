<?php

namespace Webovac\Core\Model\File;

use App\Model\File\File;


trait CoreFileData
{
	public ?int $id;
	public string $identifier;
	public ?string $compatibleIdentifier;
	public ?string $modernIdentifier;
	public string $name;
	public string|null $extension;
	public string $contentType;
	public string $type;
	public int|string|null $createdByPerson;
	public int|string|null $updatedByPerson;
	public ?\DateTimeInterface $createdAt;
	public ?\DateTimeInterface $updatedAt;


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