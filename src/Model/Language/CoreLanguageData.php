<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Language;

use App\Model\LanguageTranslation\LanguageTranslationData;
use DateTimeInterface;


trait CoreLanguageData
{
	public ?int $id;
	public string $shortcut;
	public string $name;
	public int $rank;
	/** @var array<LanguageTranslationData|array> */ public array $translations;
	public int|string|null $createdByPerson;
	public int|string|null $updatedByPerson;
	public ?DateTimeInterface $createdAt;
	public ?DateTimeInterface $updatedAt;
}