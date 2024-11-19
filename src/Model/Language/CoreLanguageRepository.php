<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Language;

use App\Model\Language\Language;
use App\Model\Language\LanguageData;
use App\Model\Web\WebData;


trait CoreLanguageRepository
{
	public function getByParameters(?array $parameters = null, ?string $path = null, ?WebData $webData = null): ?Language
	{
		return $this->getBy(['shortcut' => $parameters['LanguageDetail']]);
	}


	public function getByData(LanguageData|string $data): ?Language
	{
		return $this->getBy(['shortcut' => $data instanceof LanguageData ? $data->shortcut : $data]);
	}
}
