<?php

namespace Webovac\Core\Model;

use App\Model\Language\LanguageData;
use Webovac\Core\Control\BaseControl;

interface Renderable
{
	public function getComponent(LanguageData $languageData, string $moduleClass, string $templateName): BaseControl;
	public function getPageName(): string;
	public function getEntityIcon(): string;
	public function getParameters(?LanguageData $languageData = null): array;
}
