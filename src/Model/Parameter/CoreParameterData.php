<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Parameter;

use DateTimeInterface;
use Stepapo\Utils\Attribute\KeyProperty;
use Stepapo\Utils\Attribute\ValueProperty;


trait CoreParameterData
{
	public ?int $id;
	#[KeyProperty] public string $query;
	#[ValueProperty] public string $parameter;
	public int|string|null $createdByPerson;
	public int|string|null $updatedByPerson;
	public ?DateTimeInterface $createdAt;
	public ?DateTimeInterface $updatedAt;
}
