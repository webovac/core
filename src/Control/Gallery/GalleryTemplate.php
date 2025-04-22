<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Gallery;

use App\Control\BaseTemplate;
use App\Model\File\File;
use App\Model\Language\LanguageData;
use Nextras\Orm\Collection\ICollection;


class GalleryTemplate extends BaseTemplate
{
	/** @var File[] */ public ICollection $files;
	public int $fileCount;
	public array $urls;
	public LanguageData $languageData;
}
