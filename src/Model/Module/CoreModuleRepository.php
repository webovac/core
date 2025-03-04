<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Module;

use App\Model\Module\Module;
use App\Model\Module\ModuleData;
use App\Model\Page\PageRepository;
use App\Model\Person\Person;


trait CoreModuleRepository
{
	public function postProcessFromData(ModuleData $data, Module $module, ?Person $person = null, bool $skipDefaults = false): Module
	{
		$module->homePage = $this->getModel()->getRepository(PageRepository::class)->getBy(['module' => $module, 'name' => $data->homePage]);
		$this->persist($module);
		foreach ($module->pages as $page) {
			if (!array_key_exists($page->name, $data->pages)) {
				if (!$skipDefaults) {
					$this->getModel()->getRepository(PageRepository::class)->delete($page);
				}
				continue;
			}
			$this->getModel()->getRepository(PageRepository::class)->postProcessFromData($data->pages[$page->name], $page, skipDefaults: $skipDefaults);
		}
		return $module;
	}


	public function getByData(ModuleData|string $data): ?Module
	{
		return $this->getBy(['name' => $data instanceof ModuleData ? $data->name : $data]);
	}
}
