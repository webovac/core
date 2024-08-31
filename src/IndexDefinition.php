<?php

declare(strict_types=1);

namespace Webovac\Core;

use Webovac\Core\Model\CmsEntity;


class IndexDefinition
{
	public CmsEntity $entity;
	public string $entityName;
	/** @var IndexTranslationDefinition[] */ public array $translations;
}