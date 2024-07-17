<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Language;

use App\Model\Language\Language;
use App\Model\Language\LanguageData;


trait CoreLanguageRepository
{
	public function getByParameters(array $parameters): ?Language
	{
		return $this->getBy(['shortcut' => $parameters['LanguageDetail']]);
	}


	public function getByData(LanguageData|string $data): ?Language
	{
		return $this->getBy(['shortcut' => $data instanceof LanguageData ? $data->shortcut : $data]);
	}
}
