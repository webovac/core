<?php

declare(strict_types=1);

namespace Webovac\Core\Model\WebTranslation;

use DateTimeInterface;
use Stepapo\Utils\Attribute\KeyProperty;


trait CoreWebTranslationData
{
	public ?int $id;
	#[KeyProperty] public int|string $language;
	public string $title;
	public ?string $footer;
	public int|string|null $createdByPerson;
	public int|string|null $updatedByPerson;
	public ?DateTimeInterface $createdAt;
	public ?DateTimeInterface $updatedAt;
}