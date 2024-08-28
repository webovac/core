<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Page;

use App\Model\DataModel;
use App\Model\File\FileData;
use App\Model\Language\LanguageData;
use App\Model\Page\Page;
use App\Model\PageTranslation\PageTranslationData;
use App\Model\QueryName\QueryNameData;
use App\Model\Web\WebData;
use DateTimeInterface;
use Nette\Application\IPresenter;
use Nette\Http\FileUpload;
use Nette\Utils\Arrays;
use ReflectionException;
use Stepapo\Utils\Attribute\ArrayOfType;
use Stepapo\Utils\Attribute\DefaultValue;
use Stepapo\Utils\Attribute\KeyProperty;
use Webovac\Core\Exception\LoginRequiredException;
use Webovac\Core\Exception\MissingPermissionException;
use Webovac\Core\Lib\CmsUser;
use Webovac\Core\Model\CmsEntity;


trait CorePageData
{
	public ?int $id;
	#[KeyProperty] public string $name;
	public int $rank;
	#[DefaultValue(Page::TYPE_PAGE)] public string $type;
	#[DefaultValue(Page::ACCESS_FOR_ALL)] public string $accessFor;
	public ?string $authorizingTag;
	public ?string $style;
	/** @var QueryNameData[] */ #[ArrayOfType(QueryNameData::class)] public array|null $queryNames;
	/** @var PageTranslationData[] */ #[ArrayOfType(PageTranslationData::class)] public array|null $translations;
	/** @var int[] */ public array|null $authorizedRoles;
	/** @var int[] */ public array|null $authorizedPersons;
	#[DefaultValue(false)] public bool $hideInNavigation;
	#[DefaultValue(false)] public bool $providesNavigation;
	#[DefaultValue(false)] public bool $providesButtons;
	#[DefaultValue(false)] public bool $hasParameter;
	#[DefaultValue(false)] public bool $stretched;
	#[DefaultValue(false)] public bool $dontInheritPath;
	#[DefaultValue(false)] public bool $dontInheritAccessSetup;
	public ?string $icon;
	public ?string $repository;
	public int|string|null $parentPage;
	public int|string|null $redirectPage;
	public int|string|null $targetPage;
	public FileUpload|FileData|string|int|null $imageFile;
	public ?string $targetParameter;
	public ?string $targetUrl;
	public ?string $targetPath;
	public ?string $targetSignal;
	public int|string|null $createdByPerson;
	public int|string|null $updatedByPerson;
	public ?DateTimeInterface $createdAt;
	public ?DateTimeInterface $updatedAt;

	### for CachedModel ###

	public int|string|null $web;
	public int|string|null $module;
	public ?string $host;
	public ?string $basePath;
	/** @var AccessSetup[] */ public array|null $accessSetups;
	#[DefaultValue(false)] public bool $isHomePage;
	#[DefaultValue(false)] public bool $isDetailRoot;
	#[DefaultValue(false)] public bool $hasPath;
	public ?int $navigationPage;
	public ?int $buttonsPage;
	/** @var int[] */ public array|null $parentPages;
	/** @var int[] */ public array|null $parentDetailRootPages;


	/**
	 * @throws LoginRequiredException
	 * @throws MissingPermissionException
	 */ 
	public function checkRequirements(CmsUser $cmsUser): void
	{
		foreach ($this->accessSetups as $accessSetup) {
			$accessSetup->checkRequirements($cmsUser);
		}
	}


	public function isUserAuthorized(CmsUser $cmsUser): bool
	{
		foreach ($this->accessSetups as $accessSetup) {
			if (!$accessSetup->isUserAuthorized($cmsUser)) {
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
			Page::TYPE_SIGNAL => $presenter->link('//' . $p->targetSignal . '!'),
			Page::TYPE_EXTERNAL_LINK => $p->targetUrl,
			Page::TYPE_PAGE => $presenter->link('//default', [
					'pageName' => $p->name,
					'lang' => $languageData->shortcut,
					'id' => $parameter,
					'path' => $path,
				],
			),
			default => null,
		};
	}


	public function getClass(string $context, bool $checkActive, IPresenter $presenter, ?CmsEntity $entity, ?CmsEntity $linkedEntity = null): string
	{
		return match($context) {
			'buttons' => 'btn btn-outline-' . ($this->style ?: 'primary'),
			'signpost' => 'rounded g-col-6 g-col-lg-4 bg-' . ($this->style ? ($this->style . '-subtle') : 'light') .  ' p-3',
			default => 'menu-item' . ($this->style ? ' btn btn-subtle-' . $this->style : ''),
		}
			. (($this->id === $presenter->pageData->id && (!$linkedEntity || $linkedEntity === $entity))
			|| ($checkActive && $this->isActive($entity, $linkedEntity, $presenter, $this->targetPath))
			|| ($checkActive && $this->targetPage && $this->isActive($entity, $linkedEntity, $presenter, $this->targetPath)) ? ' active' : '')
			;
	}


	private function isActive(?CmsEntity $entity, ?CmsEntity $linkedEntity, IPresenter $presenter, ?string $path = null)
	{
		if ($linkedEntity && $linkedEntity !== $entity) {
			return false;
		}
		return (!$path || str_contains($presenter->getParameter('path') ?: '', $path)) && $presenter->getComponent('core-breadcrumbs')->isActivePage($this->targetPage ?: $this->id);
	}
}
