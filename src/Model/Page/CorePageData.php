<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Page;

use App\Model\File\FileData;
use App\Model\Page\Page;
use App\Model\PageTranslation\PageTranslationData;
use DateTimeInterface;
use Nette\Http\FileUpload;
use Stepapo\Utils\Attribute\ArrayOfType;
use Stepapo\Utils\Attribute\DefaultValue;
use Stepapo\Utils\Attribute\KeyProperty;
use Webovac\Core\Exception\LoginRequiredException;
use Webovac\Core\Exception\MissingPermissionException;
use Webovac\Core\Lib\CmsUser;


trait CorePageData
{
	public ?int $id;
	#[KeyProperty] public string $name;
	public int $rank;
	#[DefaultValue(Page::TYPE_PAGE)] public string $type;
	#[DefaultValue(Page::ACCESS_FOR_ALL)] public string $accessFor;
	public ?string $authorizingTag;
	public ?string $authorizingParentTag;
	public ?string $style;
	/** @var PageTranslationData[] */ #[ArrayOfType(PageTranslationData::class)] public array|null $translations;
	/** @var int[] */ public array|null $authorizedRoles;
	/** @var int[] */ public array|null $authorizedPersons;
	#[DefaultValue(false)] public bool $hasParameter;
	#[DefaultValue(false)] public bool $hasParentParameter;
	#[DefaultValue(false)] public bool $hideInNavigation;
	#[DefaultValue(false)] public bool $providesNavigation;
	#[DefaultValue(false)] public bool $providesButtons;
	#[DefaultValue(false)] public bool $stretched;
	#[DefaultValue(false)] public bool $dontInheritPath;
	#[DefaultValue(false)] public bool $dontInheritAccessSetup;
	public ?string $icon;
	public ?string $repository;
	public ?string $parentRepository;
	public int|string|null $parentPage;
	public int|string|null $redirectPage;
	public int|string|null $targetPage;
	public FileUpload|FileData|string|int|null $imageFile;
	public ?string $targetParameter;
	public ?string $targetParentParameter;
	public ?string $targetUrl;
	public ?string $targetSignal;
	public int|string|null $createdByPerson;
	public int|string|null $updatedByPerson;
	public ?DateTimeInterface $createdAt;
	public ?DateTimeInterface $updatedAt;

	### for CachedModel ###

	public ?int $web;
	public ?string $host;
	public ?string $basePath;
	/** @var AccessSetup[] */ public array|null $accessSetups;
	#[DefaultValue(false)] public bool $isHomePage;
	public ?int $navigationPage;
	public ?int $buttonsPage;
	/** @var int[] */ public array|null $parentPages;


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
	 * @throws \ReflectionException
	 */
	public function getLanguageIds(): array
	{
		$return = [];
		foreach ($this->getCollection('translations') as $translation) {
			$return[] = $translation->language;
		}
		return $return;
	}
}
