<?php

declare(strict_types=1);

namespace Webovac\Core\Lib;

use Build\Model\Language\Language;
use Build\Model\Language\LanguageDataRepository;
use Build\Model\Layout\Layout;
use Build\Model\Layout\LayoutDataRepository;
use Build\Model\Module\Module;
use Build\Model\Page\Page;
use Build\Model\Page\PageDataRepository;
use Build\Model\Theme\Theme;
use Build\Model\Theme\ThemeDataRepository;
use Build\Model\Web\Web;
use Build\Model\Web\WebDataRepository;
use Stepapo\Utils\Service;


class CacheRefresher implements Service
{
	public function __construct(
		private ModeChecker $modeChecker,
		private PageDataRepository $pageDataRepository,
		private WebDataRepository $webDataRepository,
		private LanguageDataRepository $languageDataRepository,
		private LayoutDataRepository $layoutDataRepository,
		private ThemeDataRepository $themeDataRepository,
		private RouteSetupProvider $routeSetupProvider,
	) {}



	public function refreshCacheWithPage(?Page $page = null): void
	{
		ini_set('memory_limit', '512M');
		if ($this->modeChecker->isTest()) {
			return;
		}
		$this->pageDataRepository->buildCache($page?->web);
		$this->webDataRepository->buildCache($page?->web);
		$this->routeSetupProvider->getSetup();
	}


	public function refreshCacheWithModule(?Module $module = null): void
	{
		ini_set('memory_limit', '512M');
		if ($this->modeChecker->isTest()) {
			return;
		}
		$this->pageDataRepository->buildCache();
		$this->webDataRepository->buildCache();
		$this->routeSetupProvider->getSetup();
	}


	public function refreshCacheWithWeb(?Web $web = null): void
	{
		ini_set('memory_limit', '512M');
		if ($this->modeChecker->isTest()) {
			return;
		}
		$this->pageDataRepository->buildCache($web);
		$this->webDataRepository->buildCache($web);
		$this->routeSetupProvider->getSetup();
	}


	public function refreshCacheWithLanguage(?Language $language = null): void
	{
		ini_set('memory_limit', '512M');
		if ($this->modeChecker->isTest()) {
			return;
		}
		$this->languageDataRepository->buildCache();
		$this->pageDataRepository->buildCache();
		$this->webDataRepository->buildCache();
		$this->layoutDataRepository->buildCache();
		$this->routeSetupProvider->getSetup();
	}


	public function refreshCache(): void
	{
		ini_set('memory_limit', '512M');
		$this->languageDataRepository->buildCache();
		$this->pageDataRepository->buildCache();
		$this->webDataRepository->buildCache();
		$this->layoutDataRepository->buildCache();
		$this->themeDataRepository->buildCache();
		$this->routeSetupProvider->getSetup();
	}


	public function refreshCacheWithLayout(?Layout $layout = null): void
	{
		if ($this->modeChecker->isTest()) {
			return;
		}
		$this->layoutDataRepository->buildCache();
		$this->themeDataRepository->buildCache();
	}


	public function refreshCacheWithTheme(?Theme $theme = null): void
	{
		if ($this->modeChecker->isTest()) {
			return;
		}
		$this->layoutDataRepository->buildCache();
		$this->themeDataRepository->buildCache();
	}
}