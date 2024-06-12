<?php

declare(strict_types=1);

namespace Webovac\Core\Model\ModuleTranslation;

use DateTimeInterface;


trait CoreModuleTranslationData
{
	public ?int $id;
	public null|int|string $language;
	public null|string $title;
	public null|string $basePath;
	public ?string $description;
	public int|string|null $createdByPerson;
	public int|string|null $updatedByPerson;
	public ?DateTimeInterface $createdAt;
	public ?DateTimeInterface $updatedAt;
}
