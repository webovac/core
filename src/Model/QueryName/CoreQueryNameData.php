<?php

declare(strict_types=1);

namespace Webovac\Core\Model\QueryName;

use DateTimeInterface;
use Stepapo\Utils\Attribute\KeyProperty;
use Stepapo\Utils\Attribute\ValueProperty;


trait CoreQueryNameData
{
	public ?int $id;
	#[KeyProperty] public string $query;
	#[ValueProperty] public string $parameter;
	public int|string|null $createdByPerson;
	public int|string|null $updatedByPerson;
	public ?DateTimeInterface $createdAt;
	public ?DateTimeInterface $updatedAt;
}
