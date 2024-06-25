<?php

declare(strict_types=1);

namespace Webovac\Core\Lib;

use App\Model\DataModel;
use App\Model\Language\LanguageData;
use Nette\Localization\Translator;


class CmsTranslator implements Translator
{
	private LanguageData $languageData;


	public function __construct(
		private DataModel $dataModel,
	) {}


	public function translate($message, ...$parameters): string
	{
		$lang = $this->languageData->shortcut;
		$string = $this->dataModel->getTextTranslation($message, $this->languageData)?->string;
		$dumped = [];
		if (!$string && !isset($dumped[$message])) {
			$dumped[$message] = true;
			bdump("$message : $lang");
		}
		return (string) ($string ?: $message);
	}


	public function setLanguageData(LanguageData $languageData): self
	{
		$this->languageData = $languageData;
		return $this;
	}
}