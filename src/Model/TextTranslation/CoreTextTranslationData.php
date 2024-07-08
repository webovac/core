<?php

declare(strict_types=1);

namespace Webovac\Core\Model\TextTranslation;

use DateTimeInterface;
use Stepapo\Utils\Attribute\KeyProperty;
use Stepapo\Utils\Attribute\ValueProperty;


trait CoreTextTranslationData
{
	public ?int $id;
	#[KeyProperty] public int|string $language;
	#[ValueProperty] public string $string;
	public int|string|null $createdByPerson;
	public int|string|null $updatedByPerson;
	public ?DateTimeInterface $createdAt;
	public ?DateTimeInterface $updatedAt;
}
