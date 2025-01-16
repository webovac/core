<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Web;

use App\Model\Language\LanguageData;
use App\Model\Page\PageDataRepository;
use App\Model\Web\WebData;
use App\Model\WebTranslation\WebTranslationDataRepository;
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
	protected function getCollection(): Collection
	{
		if (!isset($this->collection)) {
			$this->collection = $this->cache->load(lcfirst($this->getName()), function () {
				$this->cache->remove('routeSetup');
				$this->cache->remove('web_aliases');
				$collection = new Collection;
				foreach ($this->getOrmRepository()->findAll()->orderBy('basePath', ICollection::ASC_NULLS_FIRST) as $entity) {
					$collection[$this->getIdentifier($entity)] = $entity->getData();
				}
				return $collection;
			});
		}
		return $this->collection;
	}


	protected function getAliases(): array
	{
		if (!isset($this->aliases)) {
			$this->aliases = $this->cache->load(lcfirst($this->getName()) . '_aliases', function () {
				$aliases = [];
				/** @var WebData $web */
				foreach ($this->getCollection() as $web) {
					$aliases["$web->host-$web->basePath"] = $web->id;
				}
				return $aliases;
			});
		}
		return $this->aliases;
	}


	public function getId(string $host, ?string $basePath): ?int
	{
		return $this->getAliases()["$host-$basePath"] ?? null;
	}
}