<?php

declare(strict_types=1);

namespace Webovac\Core\Model\FileTranslation;

use DateTimeInterface;
use Stepapo\Utils\Attribute\KeyProperty;


trait CoreFileTranslationData
{
	public ?int $id;
	#[KeyProperty] public null|int|string $language;
	public ?string $description;
	public int|string|null $createdByPerson;
	public int|string|null $updatedByPerson;
	public ?DateTimeInterface $createdAt;
	public ?DateTimeInterface $updatedAt;
}
