<?php

declare(strict_types=1);

namespace Webovac\Core;

use DateTimeInterface;
use Webovac\Core\Model\CmsEntity;


class IndexDefinition
{
	public CmsEntity $entity;
	public string $entityName;
	public ?DateTimeInterface $date = null;
	/** @var IndexTranslationDefinition[] */ public array $translations;
}