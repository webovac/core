<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Module;

use App\Model\Module\Module;
use App\Model\Module\ModuleData;
use App\Model\Page\PageRepository;
use Nette\InvalidArgumentException;
use Stepapo\Model\Data\Item;
use Webovac\Core\Model\CmsEntity;


trait CoreModuleRepository
{
	public function postProcessFromData(Item $data, CmsEntity $entity, bool $skipDefaults = false): CmsEntity
	{
		if (!$data instanceof ModuleData || !$entity instanceof Module) {
			throw new InvalidArgumentException;
		}
		$entity->homePage = $this->getModel()->getRepository(PageRepository::class)->getBy(['module' => $entity, 'name' => $data->homePage]);
		$this->persist($entity);
		foreach ($entity->pages as $page) {
			if (!array_key_exists($page->name, $data->pages)) {
				if (!$skipDefaults) {
					$this->getModel()->getRepository(PageRepository::class)->delete($page);
				}
				continue;
			}
			$this->getModel()->getRepository(PageRepository::class)->postProcessFromData($data->pages[$page->name], $page, skipDefaults: $skipDefaults);
		}
		return $entity;
	}


	public function getByData(ModuleData|string $data): ?Module
	{
		return $this->getBy(['name' => $data instanceof ModuleData ? $data->name : $data]);
	}
}
