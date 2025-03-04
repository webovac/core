<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Web;

use App\Model\Language\LanguageData;
use App\Model\Page\PageData;
use App\Model\Page\PageDataRepository;
use App\Model\Web\WebData;
use App\Model\WebTranslation\WebTranslationDataRepository;
use Nette\Caching\Cache;
use Nette\DI\Attributes\Inject;
use Nextras\Orm\Collection\ICollection;
use Nextras\Orm\Entity\IEntity;
use Stepapo\Model\Data\Collection;
use Stepapo\Model\Data\Item;
use Throwable;


trait CoreWebDataRepository
{
	#[Inject] public WebTranslationDataRepository $webTranslationDataRepository;
	#[Inject] public PageDataRepository $pageDataRepository;
	private array $aliases;


	/**
	 * @return Collection<Item>
	 * @throws Throwable
	 */
	protected function buildCache(): void
	{
		if (isset($this->collection)) {
			return;
		}
		$this->cache->remove('routeSetup');
		$this->cache->remove('webAliases');
		$this->cache->clean([Cache::Tags => lcfirst($this->getName())]);
		$items = [];
		foreach ($this->getOrmRepository()->findAll()->orderBy('basePath', ICollection::ASC_NULLS_FIRST) as $entity) {
			$key = $this->getIdentifier($entity);
			$item = $entity->getData(forCache: true);
			$rootPageIds = [];
			foreach ($entity->getPagesForMenu() as $page) {
				$rootPageIds[] = $page->id;
			}
			$item->rootPages = $rootPageIds;
			$this->cacheItem($key, $item);
			$this->addItemToCollection($key, $item);
		}
	}


	public function getAliases(): array
	{
		if (!isset($this->aliases)) {
			$this->aliases = $this->cache->load(lcfirst($this->getName()) . 'Aliases', function () {
				$aliases = [];
				/** @var WebData $web */
				foreach ($this->getOrmRepository()->findAll()->orderBy('basePath', ICollection::ASC_NULLS_FIRST) as $web) {
					$aliases["$web->host-$web->basePath"] = $web->id;
				}
				return $aliases;
			});
		}
		return $this->aliases;
	}


	public function getKey(string $host, ?string $basePath): ?int
	{
		return $this->getAliases()["$host-$basePath"] ?? null;
	}
}