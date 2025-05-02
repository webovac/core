<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Slug;

use DateTimeInterface;
use Stepapo\Utils\Attribute\KeyProperty;
use Stepapo\Utils\Attribute\ValueProperty;


trait CoreSlugData
{
	public ?int $id;
	public string $slug;
	public bool $active;
	public ?DateTimeInterface $createdAt;
	public ?DateTimeInterface $updatedAt;
}
