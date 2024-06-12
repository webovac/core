<?php

declare(strict_types=1);

namespace Webovac\Core\Model\IndexTranslation;

use App\Model\File\FileData;
use App\Model\Page\Page;
use App\Model\PageTranslation\PageTranslationData;
use DateTimeInterface;
use Nette\Http\FileUpload;
use Webovac\Core\Attribute\DefaultValue;
use Webovac\Core\Exception\LoginRequiredException;
use Webovac\Core\Exception\MissingPermissionException;
use Webovac\Core\Lib\CmsUser;


trait CoreIndexTranslationData
{
	public ?int $id;
	public string $document;
	public ?string $documentA;
	public ?string $documentB;
	public ?string $documentC;
	public ?string $documentD;
	public ?string $documentE;
	public int|string $language;
}
