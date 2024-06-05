<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Language;

use App\Model\Language\Language;
use App\Model\Language\LanguageData;


trait CoreLanguageRepository
{
	public function getByParameter(mixed $parameter): ?Language
	{
		return $this->getBy(['shortcut' => $parameter]);
	}


	public function getByData(LanguageData|string $data): ?Language
	{
		return $this->getBy(['shortcut' => $data instanceof LanguageData ? $data->shortcut : $data]);
	}
}
