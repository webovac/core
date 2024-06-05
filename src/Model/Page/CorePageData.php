<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Page;

use App\Model\File\FileData;
use App\Model\Page\Page;
use App\Model\PageTranslation\PageTranslationData;
use DateTimeInterface;
use Nette\Http\FileUpload;
use Webovac\Core\Attribute\DefaultValue;
use Webovac\Core\Exception\LoginRequiredException;
use Webovac\Core\Exception\MissingPermissionException;
use Webovac\Core\Lib\CmsUser;


trait CorePageData
{
	public ?int $id;
	public string $name;
	public int $rank;
	#[DefaultValue(Page::TYPE_PAGE)] public string $type;
	#[DefaultValue(Page::ACCESS_FOR_ALL)] public string $accessFor;
	public ?string $authorizingTag;
	public ?string $authorizingParentTag;
	public ?string $style;
	/** @var array<PageTranslationData|array> */ public array $translations;
	/** @var array<int> */ public array $authorizedRoles;
	/** @var array<int> */ public array $authorizedPersons;
	public bool $hasParameter;
	public bool $hasParentParameter;
	public bool $hideInNavigation;
	public bool $providesNavigation;
	public bool $providesButtons;
	public bool $stretched;
	public bool $dontInheritPath;
	public bool $dontInheritAccessSetup;
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

	public ?int $web = null;
	public ?string $host = null;
	public ?string $basePath = null;
	/** @var array<AccessSetup> */ public array $accessSetups;
	public bool $isHomePage = false;
	public ?int $navigationPage = null;
	public ?int $buttonsPage = null;
	/** @var array<int> */ public array $parentPages;


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
}
