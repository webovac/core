<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Documents;

use Stepapo\Utils\Factory;
use Webovac\Core\Model\File\HasFiles;


interface IDocumentsControl extends Factory
{
	function create(?HasFiles $hasFiles = null): DocumentsControl;
}
