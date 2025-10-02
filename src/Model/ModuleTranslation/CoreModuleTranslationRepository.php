<?php

declare(strict_types=1);

namespace Webovac\Core\Model\ModuleTranslation;

use Build\Model\Module\Module;
use Build\Model\ModuleTranslation\ModuleTranslation;
use Build\Model\ModuleTranslation\ModuleTranslationData;
use Build\Model\Web\WebData;


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
