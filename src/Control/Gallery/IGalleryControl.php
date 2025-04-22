<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Gallery;

use Stepapo\Utils\Factory;
use Webovac\Core\Model\File\HasFiles;


interface IGalleryControl extends Factory
{
	function create(?HasFiles $hasFiles = null): GalleryControl;
}
