<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Web;

use App\Model\Page\PageDataRepository;
use App\Model\WebTranslation\WebTranslationDataRepository;
use Nette\DI\Attributes\Inject;
use Nextras\Orm\Collection\ICollection;
use Stepapo\Utils\Model\Collection;
use Stepapo\Utils\Model\Item;
use Throwable;


trait CoreWebDataRepository
{
	#[Inject] public WebTranslationDataRepository $webTranslationDataRepository;
	#[Inject] public PageDataRepository $pageDataRepository;


	/**
	 * @return Collection<Item>
	 * @throws Throwable
	 */
	protected function getCollection(): Collection
	{
		if (!isset($this->collection)) {
			$this->collection = $this->cache->load(lcfirst($this->getName()), function () {
				$collection = new Collection;
				foreach ($this->getOrmRepository()->findAll()->orderBy('basePath', ICollection::ASC_NULLS_FIRST) as $entity) {
					$collection[$entity->getPersistedId()] = $entity->getData();
				}
				return $collection;
			});
		}
		return $this->collection;
	}
}