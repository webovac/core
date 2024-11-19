<?php

declare(strict_types=1);

namespace Webovac\Core\Model\PageTranslation;

use App\Model\Page\Page;
use App\Model\PageTranslation\PageTranslation;
use App\Model\PageTranslation\PageTranslationData;


trait CorePageTranslationRepository
{
	public function getByData(PageTranslationData $data, Page $page): ?PageTranslation
	{
		return $this->getBy(['page' => $page, is_int($data->language) ? 'language->id' : 'language->shortcut' => $data->language]);
	}
}
