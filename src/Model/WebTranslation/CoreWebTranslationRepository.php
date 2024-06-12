<?php

declare(strict_types=1);

namespace Webovac\Core\Model\WebTranslation;

use App\Model\Web\Web;
use App\Model\WebTranslation\WebTranslation;
use App\Model\WebTranslation\WebTranslationData;


trait CoreWebTranslationRepository
{
	public function getByData(WebTranslationData $data, ?Web $web): ?WebTranslation
	{
		if (!$web) {
			return null;
		}
		return $this->getBy(['web' => $web, is_int($data->language) ? 'language->id' : 'language->shortcut' => $data->language]);
	}
}
