<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Signal;

use DateTimeInterface;
use Stepapo\Utils\Attribute\KeyProperty;
use Stepapo\Utils\Attribute\ValueProperty;


trait CoreSignalData
{
	public ?int $id;
	#[KeyProperty] public string $name;
	#[ValueProperty] public string $signal;
	public int|string|null $createdByPerson;
	public int|string|null $updatedByPerson;
	public ?DateTimeInterface $createdAt;
	public ?DateTimeInterface $updatedAt;
}
