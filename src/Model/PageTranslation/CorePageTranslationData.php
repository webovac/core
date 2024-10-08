<?php

declare(strict_types=1);

namespace Webovac\Core\Model\PageTranslation;

use DateTimeInterface;
use Stepapo\Utils\Attribute\KeyProperty;


trait CorePageTranslationData
{
	public ?int $id;
	#[KeyProperty] public int|string $language;
	public string $title;
	public ?string $description;
	public ?string $onclick;
	public ?string $path;
	public ?string $fullPath;
	public ?string $content;
	public int|string|null $createdByPerson;
	public int|string|null $updatedByPerson;
	public ?DateTimeInterface $createdAt;
	public ?DateTimeInterface $updatedAt;
}
