<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Page;

use Build\Model\Asset\AssetData;
use Build\Model\DataModel;
use Build\Model\Language\LanguageData;
use Build\Model\Page\Page;
use Build\Model\Page\PageData;
use Build\Model\Web\WebData;
use Nette\Application\IPresenter;
use Nette\Utils\Arrays;
use ReflectionException;
use Stepapo\Model\Data\Collection;
use Stepapo\Utils\Attribute\DefaultValue;
use Webovac\Core\Exception\LoginRequiredException;
use Webovac\Core\Exception\MissingPermissionException;
use Webovac\Core\Lib\CmsUser;
use Webovac\Core\Lib\PageActivator;
use Webovac\Core\Model\CmsEntity;
use Webovac\Core\Model\HasRequirements;


trait CorePageData
{
	public ?string $host;
	public ?string $basePath;
	public ?int $defaultLanguage;
	/** @var AccessSetup[] */ public array|null $accessSetups;
	/** @var AssetData[] */ public array $stylesheets = [];
	/** @var AssetData[] */ public array $scripts = [];
	#[DefaultValue(false)] public bool $isHomePage;
	#[DefaultValue(false)] public bool $isDetailRoot;
	#[DefaultValue(false)] public bool $hasPath;
	public ?int $menuPage;
	public ?int $navigationPage;
	public ?int $buttonsPage;
	/** @var int[] */ public array|null $parentPages;
	/** @var int[] */ public array|null $parentDetailRootPages;
	/** @var int[] */ public array|null $childPageIds;


	/**
	 * @throws ReflectionException
	 */
	public function getLanguageIds(): array
	{
		$return = [];
		foreach ($this->getCollection('translations') as $translation) {
			$return[] = $translation->language;
		}
		return $return;
	}


	public function getHref(LanguageData $languageData, WebData $webData, DataModel $dataModel, IPresenter $presenter, ?CmsEntity $entity, ?CmsEntity $linkedEntity = null): ?string
	{
		$e = $linkedEntity ?: $entity;
		$anchor = null;
		if ($this->type === Page::TYPE_INTERNAL_LINK && $this->targetPage) {
			$p = $dataModel->getPageData($webData->id, $this->targetPage);
			$path = $this->targetPath;
			$parameter = $this->targetParameter ? [$e->getPageName() => $this->targetParameter] : null;
			$anchor = $this->targetAnchor;
		} else {
			$p = $this;
			$parameter = $p->hasParameter && !isset($presenter->path) ? $e?->getParameters() : null;
			$path = $p->hasPath && isset($presenter->path) ? ($presenter->path . '/' . Arrays::first($e->getParameters())) : '';
		}
		return match($p->type) {
			Page::TYPE_SIGNAL => $presenter->getName() === 'Core:Error4xx' ? null : $presenter->link('//' . $p->targetSignal . '!'),
			Page::TYPE_EXTERNAL_LINK => $p->targetUrl,
			Page::TYPE_PAGE => $presenter->link('//Home:' . ($anchor ? '#' . $anchor : ''), [
					'pageName' => $p->name,
					'lang' => $languageData->shortcut,
					'id' => $parameter,
					'path' => $path,
				],
			),
			default => null,
		};
	}


	public function getClass(string $context, bool $checkActive, IPresenter $presenter, PageActivator $pageActivator, ?CmsEntity $entity, ?CmsEntity $linkedEntity = null): string
	{
		# TODO fix targetPage
		return match($context) {
			'buttons' => 'btn btn-outline-' . ($this->style ?: 'primary'),
			'signpost' => 'g-col-6 g-col-lg-4 bg-' . ($this->style ? ($this->style . '-subtle') : 'light') .  ' p-3',
			default => 'menu-item' . ($this->style ? ' btn btn-subtle-' . $this->style : ''),
		}
			. ((!$this->targetPath && !$this->targetAnchor && ($this->id === $presenter->pageData->id || $this->targetPage === $presenter->pageData->id) && (!$linkedEntity || $linkedEntity === $entity))
			|| ($checkActive && $this->isActive($entity, $linkedEntity, $presenter, $pageActivator, $this->targetPath))
			|| ($checkActive && $this->targetPage && $this->isActive($entity, $linkedEntity, $presenter, $pageActivator, $this->targetPath)) ? ' active' : '')
			;
	}


	/** @return Collection<PageData> */
	public function getChildPageDatas(DataModel $dataModel, WebData $webData, CmsUser $cmsUser): Collection
	{
		$pageDatas = [];
		foreach ($this->childPageIds as $childPageId) {
			$pageDatas[] = $dataModel->getPageData($webData->id, $childPageId);
		}
		uasort($pageDatas, fn(PageData $a, PageData $b) => $a->rank <=> $b->rank);
		return new Collection($pageDatas);
	}


	private function isActive(?CmsEntity $entity, ?CmsEntity $linkedEntity, IPresenter $presenter, PageActivator $pageActivator, ?string $path = null)
	{
		if ($linkedEntity && $linkedEntity !== $entity) {
			return false;
		}
		return (!$path || str_contains($presenter->path ?? '', $path)) && $pageActivator->isActivePage($this->targetPage ?: $this->id);
	}


	public function loadAsset(string $name): bool
	{
		return array_key_exists($name, $this->assets);
	}


	public function hasStyle(): bool
	{
		return (bool) $this->style;
	}
}
