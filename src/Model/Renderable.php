<?php

namespace Webovac\Core\Model;

use App\Model\Language\LanguageData;
use Webovac\Core\Control\BaseControl;

interface Renderable
{
	public function getComponent(LanguageData $languageData, string $moduleClass, string $templateName): BaseControl;
}
