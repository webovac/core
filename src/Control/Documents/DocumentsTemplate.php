<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Documents;

use App\Control\BaseTemplate;
use App\Model\File\File;
use App\Model\Language\LanguageData;


class DocumentsTemplate extends BaseTemplate
{
	/** @var File[] */ public array $files;
	public int $fileCount;
	public LanguageData $languageData;
}
