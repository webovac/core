<?php

declare(strict_types=1);

namespace Webovac\Core\Model\ModuleTranslation;

use App\Model\Module\Module;
use App\Model\ModuleTranslation\ModuleTranslation;
use App\Model\ModuleTranslation\ModuleTranslationData;
use App\Model\Web\WebData;


trait CoreModuleTranslationRepository
{
	public function getByData(ModuleTranslationData $data, ?Module $module): ?ModuleTranslation
	{
		if (!$module) {
			return null;
		}
		return $this->getBy(['module' => $module, is_int($data->language) ? 'language->id' : 'language->shortcut' => $data->language]);
	}


	public function getFilterByWeb(WebData $webData): array
	{
		return ['module->webs->id' => $webData->id];
	}
}
