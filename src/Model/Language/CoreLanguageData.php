<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Language;

use App\Model\LanguageTranslation\LanguageTranslationData;
use DateTimeInterface;
use Webovac\Core\Attribute\ArrayOfType;


trait CoreLanguageData
{
	public ?int $id;
	public string $shortcut;
	public string $name;
	public int $rank;
	#[ArrayOfType(LanguageTranslationData::class, 'translationLanguage')] /** @var LanguageTranslationData[] */ public array $translations;
	public int|string|null $createdByPerson;
	public int|string|null $updatedByPerson;
	public ?DateTimeInterface $createdAt;
	public ?DateTimeInterface $updatedAt;
}