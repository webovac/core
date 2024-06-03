<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Module;

use App\Model\Module\Module;
use App\Model\Module\ModuleData;
use App\Model\Page\PageRepository;
use App\Model\Person\Person;
use Webovac\Core\Model\CmsData;
use Webovac\Core\Model\CmsDataRepository;


trait CoreModuleRepository
{
	public function getByParameter(mixed $parameter)
	{
		return $this->getBy(['id' => $parameter]);
	}


	public function postProcessFromData(ModuleData $data, Module $module, ?Person $person = null, string $mode = CmsDataRepository::MODE_INSTALL): Module
	{
		$module->homePage = $this->getModel()->getRepository(PageRepository::class)->getBy(['module' => $module, 'name' => $data->homePage]);
		$this->persist($module);
		foreach ($module->pages as $page) {
			if (!array_key_exists($page->name, $data->pages)) {
				if ($mode === CmsDataRepository::MODE_INSTALL) {
					$this->getModel()->getRepository(PageRepository::class)->delete($page);
				}
				continue;
			}
			$this->getModel()->getRepository(PageRepository::class)->postProcessFromData($data->pages[$page->name], $page, mode: $mode);
		}
		return $module;
	}


	public function getByData(ModuleData|string $data): ?Module
	{
		return $this->getBy(['name' => $data instanceof ModuleData ? $data->name : $data]);
	}
}
