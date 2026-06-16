<?php

declare(strict_types=1);

namespace Webovac\Core\Control\ContentForm;

use Stepapo\Utils\Factory;
use Webovac\Core\Model\HasTranslations;


interface IContentFormControl extends Factory
{
	function create(HasTranslations $hasTranslations): ContentFormControl;
}
