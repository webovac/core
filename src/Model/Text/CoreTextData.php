<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Text;

use App\Model\TextTranslation\TextTranslationData;
use DateTimeInterface;
use Stepapo\Utils\Attribute\ArrayOfType;
use Stepapo\Utils\Attribute\KeyProperty;


trait CoreTextData
{
	public ?int $id;
	#[KeyProperty] public string $name;
	/** @var TextTranslationData[] */ #[ArrayOfType(TextTranslationData::class)] public array|null $translations;
	public int|string|null $createdByPerson;
	public int|string|null $updatedByPerson;
	public ?DateTimeInterface $createdAt;
	public ?DateTimeInterface $updatedAt;

	public static function createFromArray(mixed $config = [], mixed $key = null, bool $skipDefaults = false, mixed $parentKey = null): static
	{
		$config = isset($config['translations']) ? $config : ['translations' => (array) $config];
		return parent::createFromArray($config, $key, $skipDefaults, $parentKey);
	}
}
