<?php

declare(strict_types=1);

namespace Webovac\Core\Model\PageTranslation;

use App\Model\Path\PathData;
use DateTimeInterface;
use Stepapo\Utils\Attribute\ArrayOfType;
use Stepapo\Utils\Attribute\KeyProperty;
use Stepapo\Utils\Attribute\SkipInManipulation;


trait CorePageTranslationData
{
	public ?int $id;
	#[KeyProperty] public int|string $language;
	/** @var PathData[] */ #[ArrayOfType(PathData::class),SkipInManipulation] public array|null $paths;
	public string $title;
	public ?string $description;
	public ?string $onclick;
	public ?string $path;
	public ?string $content;
	public int|string|null $createdByPerson;
	public int|string|null $updatedByPerson;
	public ?DateTimeInterface $createdAt;
	public ?DateTimeInterface $updatedAt;
}
