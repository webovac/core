<?php

declare(strict_types=1);

namespace Webovac\Core\Model\TextTranslation;

use Build\Model\Text\Text;
use Build\Model\TextTranslation\TextTranslation;
use Build\Model\TextTranslation\TextTranslationData;


trait CoreTextTranslationRepository
{
	public function getByData(TextTranslationData $data, Text $text): ?TextTranslation
	{
		return $this->getBy(['text' => $text, is_int($data->language) ? 'language->id' : 'language->shortcut' => $data->language]);
	}
}
