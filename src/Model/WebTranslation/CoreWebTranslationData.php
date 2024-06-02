<?php

declare(strict_types=1);

namespace Webovac\Core\Model\WebTranslation;


trait CoreWebTranslationData
{
	public ?int $id;
	public int|string $language;
	public string $title;
	public ?string $footer;
	public int|string|null $createdByPerson;
	public int|string|null $updatedByPerson;
	public ?\DateTimeInterface $createdAt;
	public ?\DateTimeInterface $updatedAt;
}