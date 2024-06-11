<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Index;

use App\Model\File\FileData;
use App\Model\IndexTranslation\IndexTranslationData;
use App\Model\Page\Page;
use App\Model\PageTranslation\PageTranslationData;
use DateTimeInterface;
use Nette\Http\FileUpload;
use Webovac\Core\Attribute\DefaultValue;
use Webovac\Core\Exception\LoginRequiredException;
use Webovac\Core\Exception\MissingPermissionException;
use Webovac\Core\Lib\CmsUser;


trait CoreIndexData
{
	public ?int $id;
	public int|string|null $language;
	public int|string|null $module;
	public int|string|null $page;
	public int|string|null $web;
	/** @var array<IndexTranslationData|array> */ public array $translations;
}
