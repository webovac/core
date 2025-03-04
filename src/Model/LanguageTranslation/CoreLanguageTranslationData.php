<?php

declare(strict_types=1);

namespace Webovac\Core\Model\LanguageTranslation;

use DateTimeInterface;
use Stepapo\Utils\Attribute\KeyProperty;


trait CoreLanguageTranslationData
{
	public ?int $id;
	#[KeyProperty] public int|string|null $translationLanguage;
	public string $title;
	public int|string|null $createdByPerson;
	public int|string|null $updatedByPerson;
	public ?DateTimeInterface $createdAt;
	public ?DateTimeInterface $updatedAt;
}