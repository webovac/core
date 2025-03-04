<?php

declare(strict_types=1);

namespace Webovac\Core\Lib;

use App\Model\Language\LanguageData;
use App\Model\Layout\LayoutData;
use App\Model\Page\PageData;
use App\Model\Theme\ThemeData;
use App\Model\Web\WebData;
use Stepapo\Utils\Service;


class DataProvider implements Service
{
	private WebData $webData;
	private LanguageData $languageData;
	private ?PageData $navigationPageData;
	private ?PageData $buttonsPageData;
	private ?PageData $pageData;
	private LayoutData $layoutData;
	private ThemeData $themeData;


	public function getWebData(): WebData
	{
		return $this->webData;
	}


	public function setWebData(WebData $webData): self
	{
		$this->webData = $webData;
		return $this;
	}


	public function getLanguageData(): LanguageData
	{
		return $this->languageData;
	}


	public function setLanguageData(LanguageData $languageData): self
	{
		$this->languageData = $languageData;
		return $this;
	}


	public function getPageData(): ?PageData
	{
		return $this->pageData;
	}


	public function setPageData(?PageData $pageData): self
	{
		$this->pageData = $pageData;
		return $this;
	}


	public function getNavigationPageData(): ?PageData
	{
		return $this->navigationPageData;
	}
	

	public function setNavigationPageData(?PageData $navigationPageData): self
	{
		$this->navigationPageData = $navigationPageData;
		return $this;
	}


	public function getButtonsPageData(): ?PageData
	{
		return $this->buttonsPageData;
	}


	public function setButtonsPageData(?PageData $buttonsPageData): self
	{
		$this->buttonsPageData = $buttonsPageData;
		return $this;
	}


	public function getLayoutData(): LayoutData
	{
		return $this->layoutData;
	}


	public function setLayoutData(LayoutData $layoutData): self
	{
		$this->layoutData = $layoutData;
		return $this;
	}


	public function getThemeData(): ThemeData
	{
		return $this->themeData;
	}


	public function setThemeData(ThemeData $themeData): self
	{
		$this->themeData = $themeData;
		return $this;
	}
}