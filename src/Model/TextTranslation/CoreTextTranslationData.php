<?php

declare(strict_types=1);

namespace Webovac\Core\Model\TextTranslation;

use DateTimeInterface;


trait CoreTextTranslationData
{
	public ?int $id;
	public int|string $language;
	public string $string;
	public int|string|null $createdByPerson;
	public int|string|null $updatedByPerson;
	public ?DateTimeInterface $createdAt;
	public ?DateTimeInterface $updatedAt;
}
