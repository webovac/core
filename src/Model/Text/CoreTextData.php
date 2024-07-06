<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Text;

use App\Model\TextTranslation\TextTranslationData;
use DateTimeInterface;
use Stepapo\Utils\Attribute\ArrayOfType;


trait CoreTextData
{
	public ?int $id;
	public string $name;
	#[ArrayOfType(TextTranslationData::class, 'language')] /** @var TextTranslationData[] */ public array $translations;
	public int|string|null $createdByPerson;
	public int|string|null $updatedByPerson;
	public ?DateTimeInterface $createdAt;
	public ?DateTimeInterface $updatedAt;
}
