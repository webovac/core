<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Person;

use DateTimeInterface;


trait CorePersonData
{
	public ?int $id;
	public ?string $email;
	public string $firstName;
	public string $lastName;
	/** @var string[] */ public array|null $roles;
	public ?DateTimeInterface $createdAt;
	public ?DateTimeInterface $updatedAt;
}
