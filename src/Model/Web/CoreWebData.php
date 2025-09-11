<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Web;

use App\Model\DataModel;
use App\Model\File\File;
use App\Model\File\FileData;
use App\Model\Page\Page;
use App\Model\Page\PageData;
use App\Model\Person\Person;
use App\Model\Web\Web;
use App\Model\WebTranslation\WebTranslationData;
use DateTimeInterface;
use Stepapo\Model\Data\Collection;
use Stepapo\Utils\Attribute\ArrayOfType;
use Stepapo\Utils\Attribute\DefaultValue;
use Stepapo\Utils\Attribute\DontCache;
use Stepapo\Utils\Attribute\SkipInManipulation;
use Stepapo\Utils\Attribute\Type;
use Webovac\Core\Lib\CmsUser;
use Webovac\Core\Model\CmsEntity;


trait CoreWebData
{
	public ?int $id;
	public string $code;
	public string $host;
	public int|string|null $homePage;
	#[DefaultValue(Web::DEFAULT_COLOR)] public string $color;
	#[DefaultValue(Web::DEFAULT_COMPLEMENTARY_COLOR)] public string $complementaryColor;
	#[DefaultValue(Web::DEFAULT_ICON_BACKGROUND_COLOR)] public string $iconBackgroundColor;
	#[DefaultValue('cs')] public int|string $defaultLanguage;
	#[Type(FileData::class), DefaultValue(File::DEFAULT_ICON)] public ?FileData $iconFile;
	#[Type(FileData::class)] public ?FileData $largeIconFile;
	#[Type(FileData::class), DefaultValue(File::DEFAULT_ICON)] public ?FileData $logoFile;
	#[Type(FileData::class)] public ?FileData $backgroundFile;
	/** @var WebTranslationData[] */ #[ArrayOfType(WebTranslationData::class)] public array|null $translations;
	/** @var PageData[]|array */ #[ArrayOfType(PageData::class)] public array|null $pages;
	/** @var FileData[] */ #[ArrayOfType(FileData::class), SkipInManipulation] public array|null $files;
	/** @var string[] */ public array|null $modules;
	#[DefaultValue('~')] public string $basePath;
	#[DefaultValue(false)] public bool $isAdmin;
	#[DefaultValue(false)] public bool $disableIndex;
	#[DefaultValue(false)] public bool $disableBacklink;
	#[DefaultValue(false)] public bool $hideSidePanel;
	#[DontCache] public array $tree;
	public int|string|null $responsiblePerson;
	/** @var int[] */ public array|null $adminPersons;
	/** @var string[] */ public array|null $adminRoles;
	#[DontCache] public int|string|null $createdByPerson;
	#[DontCache] public int|string|null $updatedByPerson;
	public ?DateTimeInterface $createdAt;
	public ?DateTimeInterface $updatedAt;

	### for CachedModel ###

	/** @var int[] */ public array|null $rootPages;
	/** @var PageData[]|array */ #[ArrayOfType(PageData::class)] public array|null $allPages;


	public function getRouteMetadata(string $presenter, ?string $language = null, string $action = 'default'): array
	{
		$return = [
			'presenter' => $presenter,
			'action' => $action,
			'host' => $this->host,
			'basePath' => $this->basePath,
		];
		if ($language) {
			$return['lang'] = $language;
		}
		return $return;
	}


	public function getBaseUrl(?string $language = null): string
	{
		return '//'
			. $this->host
			. ($this->basePath !== '~' ? ('/' . $this->basePath) : '')
			. ($language ? ('/' . $language) : '');
	}


	public function getStyleRouteMask(): string
	{
		return $this->getBaseUrl() . '/style';
	}


	public function getManifestRouteMask(?string $language = null): string
	{
		return $this->getBaseUrl($language) . '/manifest.json';
	}


	public function getAuthorizationRouteMask(?string $language = null): string
	{
		return $this->getBaseUrl($language) . '/api/v1/authorization/<action>';
	}


	public function getApiRouteMask(?string $language = null): string
	{
		return $this->getBaseUrl($language) . '/api/v1/<entityName>[/<id>[/<related>]][.<type>]';
	}


	public function getPageRouteMask(): string
	{
		return $this->getBaseUrl() . '[/<p .+>]';
	}


	/** @return Collection<PageData> */
	public function getRootPageDatas(DataModel $dataModel, CmsUser $cmsUser, ?CmsEntity $entity = null): Collection
	{
		$pageDatas = [];
		foreach ($this->rootPages as $rootPageId) {
			$pageData = $dataModel->getPageData($this->id, $rootPageId);
			$pageDataToCheck = $pageData->type === Page::TYPE_INTERNAL_LINK
				? $dataModel->getPageData($this->id, $pageData->targetPage)
				: $pageData;
			if ($pageDataToCheck->checkPageRequirements($this, $cmsUser, $entity)) {
				$pageDatas[] = $pageData;
			}
		}
		uasort($pageDatas, fn(PageData $a, PageData $b) => $a->rank <=> $b->rank);
		return new Collection($pageDatas);
	}


	public function isUserAdmin(CmsUser $cmsUser): bool
	{
		return $cmsUser->isLoggedIn() &&
			($this->isPersonAdmin($cmsUser->getPerson()) || $this->isRoleAdmin($cmsUser->getRoles()));
	}


	private function isPersonAdmin(Person $person): bool
	{
		return in_array($person->id, $this->adminPersons, true);
	}


	private function isRoleAdmin(array $roles): bool
	{
		foreach ($roles as $role) {
			if (in_array($role, $this->adminRoles, true)) {
				return true;
			}
		}
		return false;
	}


//	public static function createFromArray(mixed $config = [], mixed $key = null, bool $skipDefaults = false, mixed $parentKey = null): static
//	{
//		foreach (['iconFile', 'logoFile', 'backgroundFile'] as $name) {
//			if (isset($config[$name]) and is_string($config[$name])) {
//				$upload = $config[$name];
//				$config[$name] = new FileData;
//				$config[$name]->upload = $upload;
//			}
//		}
//		return parent::createFromArray($config, $key, $skipDefaults, $parentKey);
//	}
}