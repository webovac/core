<?php

namespace Webovac\Core\Model\LanguageTranslation;


trait CoreLanguageTranslationData
{
	public ?int $id;
	public int|string|null $translationLanguage;
	public string $title;
	public int|string|null $createdByPerson;
	public int|string|null $updatedByPerson;
	public ?\DateTimeInterface $createdAt;
	public ?\DateTimeInterface $updatedAt;
}