<?php

declare(strict_types=1);

namespace Webovac\Core\Ext\Migrations;

use Nextras\Migrations\Entities\File;


class CmsFile extends File
{
	/** @var CmsGroup */ public $group;
	/** @var string */ public $extension;
	/** @var string */ public $name;
	/** @var string */ public $path;
	/** @var string */ public $checksum;
}
