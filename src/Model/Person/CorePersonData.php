<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Person;


trait CorePersonData
{
	public ?int $id;
	public ?string $email;
	public string $firstName;
	public string $lastName;
	/** @var string[] */ public array $roles;
	public ?\DateTimeInterface $createdAt;
	public ?\DateTimeInterface $updatedAt;
}
