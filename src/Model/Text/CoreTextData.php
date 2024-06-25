<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Text;

use App\Model\TextTranslation\TextTranslationData;
use App\Model\Page\PageData;
use DateTimeInterface;


trait CoreTextData
{
	public ?int $id;
	public string $name;
	/** @var TextTranslationData[]|array */ public array $translations;
	public int|string|null $createdByPerson;
	public int|string|null $updatedByPerson;
	public ?DateTimeInterface $createdAt;
	public ?DateTimeInterface $updatedAt;
}
