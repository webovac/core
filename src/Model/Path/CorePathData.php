<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Path;

use DateTimeInterface;


trait CorePathData
{
	public ?int $id;
	public string $path;
	public bool $active;
	public ?DateTimeInterface $createdAt;
	public ?DateTimeInterface $updatedAt;
}
