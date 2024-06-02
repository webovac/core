<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Module;

use App\Model\ModuleTranslation\ModuleTranslationData;
use App\Model\Page\PageData;


trait CoreModuleData
{
	public ?int $id;
	public string $name;
	public int|string $homePage;
	/** @var array<ModuleTranslationData|array> */ public array $translations;
	/** @var array<PageData|array> */ public array $pages;
	public ?string $icon;
	public array $tree;
	public int|string|null $createdByPerson;
	public int|string|null $updatedByPerson;
	public ?\DateTimeInterface $createdAt;
	public ?\DateTimeInterface $updatedAt;
}
