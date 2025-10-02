<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Documents;

use Build\Control\BaseTemplate;
use Build\Model\File\File;
use Build\Model\Language\LanguageData;


class DocumentsTemplate extends BaseTemplate
{
	/** @var File[] */ public array $files;
	public int $fileCount;
	public LanguageData $languageData;
}
