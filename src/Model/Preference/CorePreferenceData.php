<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Preference;

use DateTimeInterface;


trait CorePreferenceData
{
	public ?int $id;
	public ?DateTimeInterface $createdAt;
	public ?DateTimeInterface $updatedAt;
}