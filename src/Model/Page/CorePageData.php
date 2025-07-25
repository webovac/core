<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Page;

use App\Model\Asset\AssetData;
use App\Model\DataModel;
use App\Model\File\FileData;
use App\Model\Language\LanguageData;
use App\Model\Page\Page;
use App\Model\Page\PageData;
use App\Model\PageTranslation\PageTranslationData;
use App\Model\Parameter\ParameterData;
use App\Model\Signal\SignalData;
use App\Model\Web\WebData;
use DateTimeInterface;
use Nette\Application\IPresenter;
use Nette\Utils\Arrays;
use ReflectionException;
use Stepapo\Model\Data\Collection;
use Stepapo\Utils\Attribute\ArrayOfType;
use Stepapo\Utils\Attribute\DefaultValue;
use Stepapo\Utils\Attribute\DontCache;
use Stepapo\Utils\Attribute\KeyProperty;
use Stepapo\Utils\Attribute\SkipInManipulation;
use Stepapo\Utils\Attribute\Type;
use Webovac\Core\Exception\LoginRequiredException;
use Webovac\Core\Exception\MissingPermissionException;
use Webovac\Core\Lib\CmsUser;
use Webovac\Core\Lib\PageActivator;
use Webovac\Core\Model\CmsEntity;


trait CorePageData
{
	public ?int $id;
	#[KeyProperty] public string $name;
	public int|null $rank;
	#[DefaultValue(Page::TYPE_PAGE)] public string $type;
	#[DefaultValue(Page::ACCESS_FOR_ALL)] public string $accessFor;
	public ?string $authorizingTag;
	public ?string $style;
	/** @var ParameterData[] */ #[ArrayOfType(ParameterData::class)] public array|null $parameters;
	/** @var SignalData[] */ #[ArrayOfType(SignalData::class)] public array|null $signals;
	/** @var PageTranslationData[] */ #[ArrayOfType(PageTranslationData::class)] public array|null $translations;
	/** @var FileData[] */ #[ArrayOfType(FileData::class), SkipInManipulation] public array|null $files;
	/** @var string[] */ public array|null $libs;
	/** @var string[] */ public array|null $authorizedRoles;
	/** @var int[] */ public array|null $authorizedPersons;
	#[DefaultValue(false)] public bool $hideInNavigation;
	#[DefaultValue(false)] public bool $providesNavigation;
	#[DefaultValue(false)] public bool $providesButtons;
	#[DefaultValue(false)] public bool $hasParameter;
	#[DefaultValue(false)] public bool $stretched;
	#[DefaultValue(false)] public bool $dontInheritPath;
	#[DefaultValue(false)] public bool $dontInheritAccessSetup;
	#[DefaultValue(false)] public bool $ajax;
	public ?string $icon;
	public ?string $repository;
	public int|string|null $parentPage;
	public int|string|null $redirectPage;
	public int|string|null $targetPage;
	#[Type(FileData::class)] public ?FileData $imageFile;
	public ?string $targetParameter;
	public ?string $targetUrl;
	public ?string $targetPath;
	public ?string $targetSignal;
	public ?int $layoutWidth;
	#[DontCache] public int|string|null $createdByPerson;
	#[DontCache] public int|string|null $updatedByPerson;
	#[DontCache] public ?DateTimeInterface $createdAt;
	#[DontCache] public ?DateTimeInterface $updatedAt;

	### for CachedModel ###

	public int|string|null $web;
	public int|string|null $module;
	public int|string|null $targetModule;
	public ?string $host;
	public ?string $basePath;
	/** @var AccessSetup[] */ public array|null $accessSetups;
	/** @var AssetData[] */ public array $stylesheets = [];
	/** @var AssetData[] */ public array $scripts = [];
	#[DefaultValue(false)] public bool $isHomePage;
	#[DefaultValue(false)] public bool $isDetailRoot;
	#[DefaultValue(false)] public bool $hasPath;
	public ?int $navigationPage;
	public ?int $buttonsPage;
	/** @var int[] */ public array|null $parentPages;
	/** @var int[] */ public array|null $parentDetailRootPages;
	/** @var int[] */ public array|null $childPageIds;


	/**
	 * @throws LoginRequiredException
	 * @throws MissingPermissionException
	 */
	public function checkRequirements(CmsUser $cmsUser, WebData $webData): void
	{
		foreach ($this->accessSetups as $accessSetup) {
			$accessSetup->checkRequirements($cmsUser, $webData);
		}
	}


	public function isUserAuthorized(CmsUser $cmsUser, WebData $webData): bool
	{
		foreach ($this->accessSetups as $accessSetup) {
			if (!$accessSetup->isUserAuthorized($cmsUser, $webData)) {
				return false;
			}
		}
		return true;
	}


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
		if ($this->type === Page::TYPE_INTERNAL_LINK && $this->targetPage) {
			$p = $dataModel->getPageData($webData->id, $this->targetPage);
			$path = $this->targetPath;
			$parameter = $this->targetParameter ? [$e->getPageName() => $this->targetParameter] : null;
		} else {
			$p = $this;
			$parameter = $p->hasParameter && !$presenter->getParameter('path') ? $e?->getParameters() : null;
			$path = $p->hasPath && $presenter->getParameter('path') ? ($presenter->getParameter('path') . '/' . Arrays::first($e->getParameters())) : '';
		}
		return match($p->type) {
			Page::TYPE_SIGNAL => $presenter->getName() === 'Error4xx' ? null : $presenter->link('//' . $p->targetSignal . '!'),
			Page::TYPE_EXTERNAL_LINK => $p->targetUrl,
			Page::TYPE_PAGE => $presenter->link('//Home:', [
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
			. ((!$this->targetPath && ($this->id === $presenter->pageData->id || $this->targetPage === $presenter->pageData->id) && (!$linkedEntity || $linkedEntity === $entity))
			|| ($checkActive && $this->isActive($entity, $linkedEntity, $presenter, $pageActivator, $this->targetPath))
			|| ($checkActive && $this->targetPage && $this->isActive($entity, $linkedEntity, $presenter, $pageActivator, $this->targetPath)) ? ' active' : '')
			;
	}


	/** @return Collection<PageData> */
	public function getChildPageDatas(DataModel $dataModel, WebData $webData, CmsUser $cmsUser, ?CmsEntity $entity = null): Collection
	{
		$pageDatas = [];
		foreach ($this->childPageIds as $childPageId) {
			$pageData = $dataModel->getPageData($webData->id, $childPageId);
			$pageDataToCheck = $pageData->type === Page::TYPE_INTERNAL_LINK
				? $dataModel->getPageData($webData->id, $pageData->targetPage)
				: $pageData;
			if ($pageDataToCheck->checkPageRequirements($webData, $cmsUser, $entity)) {
				$pageDatas[] = $pageData;
			}
		}
		uasort($pageDatas, fn(PageData $a, PageData $b) => $a->rank <=> $b->rank);
		return new Collection($pageDatas);
	}


	public function checkPageRequirements(WebData $webData, CmsUser $cmsUser, ?CmsEntity $entity = null): bool
	{
		if (!$this->isUserAuthorized($cmsUser, $webData)) {
			return false;
		}
		if ($this->authorizingTag && $entity) {
			if (method_exists($entity, 'checkRequirements') && !$entity->checkRequirements($cmsUser, $webData, $this->authorizingTag)) {
				return false;
			}
		}
		return true;
	}


	private function isActive(?CmsEntity $entity, ?CmsEntity $linkedEntity, IPresenter $presenter, PageActivator $pageActivator, ?string $path = null)
	{
		if ($linkedEntity && $linkedEntity !== $entity) {
			return false;
		}
		return (!$path || str_contains($presenter->getParameter('path') ?: '', $path)) && $pageActivator->isActivePage($this->targetPage ?: $this->id);
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
