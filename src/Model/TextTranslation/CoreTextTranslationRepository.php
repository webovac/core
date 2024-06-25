<?php

declare(strict_types=1);

namespace Webovac\Core\Model\TextTranslation;

use App\Model\Text\Text;
use App\Model\TextTranslation\TextTranslation;
use App\Model\TextTranslation\TextTranslationData;


trait CoreTextTranslationRepository
{
	public function getByData(TextTranslationData $data, Text $text): ?TextTranslation
	{
		return $this->getBy(['text' => $text, is_int($data->language) ? 'language->id' : 'language->shortcut' => $data->language]);
	}
}
