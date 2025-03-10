<?php

declare(strict_types=1);

namespace Webovac\Core\Presenter\Style;

use App\Model\DataModel;
use App\Model\Layout\LayoutData;
use App\Model\Orm;
use App\Model\Preference\Preference;
use App\Model\Theme\ThemeData;
use App\Model\Web\WebData;
use Nette\Application\Attributes\Persistent;
use Nette\Application\UI\Presenter;
use Nette\DI\Attributes\Inject;
use Webovac\Core\Lib\CmsUser;
use Webovac\Core\Lib\FileUploader;
use Webovac\Core\Lib\ModuleChecker;


/**
 * @property StyleTemplate $template
 */
class StylePresenter extends Presenter
{
	#[Persistent] public string $host;
	#[Persistent] public string $basePath;
	#[Inject] public Orm $orm;
	#[Inject] public DataModel $dataModel;
	#[Inject] public FileUploader $fileUploader;
	#[Inject] public ModuleChecker $moduleChecker;
	#[Inject] public CmsUser $cmsUser;
	private ?Preference $preference;
	public ?WebData $webData;
	public LayoutData $layoutData;
	public ThemeData $themeData;


	public function actionDefault(): void
	{
		$this->webData = $this->dataModel->getWebDataByHost($this->host, $this->basePath);
		$this->layoutData = $this->dataModel->getLayoutData($this->webData->layout);
		if ($this->cmsUser->isLoggedIn()) {
			$this->preference = $this->orm->preferenceRepository->getPreference($this->webData, $this->cmsUser->getPerson());
		}
		if (
			$this->cmsUser->isLoggedIn()
			&& ($themeId = $this->preference?->theme?->id)
			&& in_array($themeId, $this->layoutData->themes)
		) {
			$this->themeData = $this->dataModel->getThemeData($themeId);
		} else {
			$this->themeData = $this->dataModel->getThemeData($this->layoutData->defaultTheme);
		}
	}


	public function renderDefault(): void
	{
		$this->template->webData = $this->webData;
		$this->template->themeData = $this->themeData;
		$this->template->backgroundUrl = $this->webData->backgroundFile
			? $this->fileUploader->getUrl($this->webData->backgroundFile->getBackgroundIdentifier(), '2160x2160', null, 50)
			: 'dist/images/fsv_background.webp';
		$this->template->colors = ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark'];
		if ($this->moduleChecker->isModuleInstalled('style')) {
			$layout = $this->dataModel->getLayoutData($this->webData->layout);
			$this->template->l = $layout;
			foreach ($layout->themes as $themeId) {
				$theme = $this->dataModel->getThemeData($themeId);
				$this->template->t[$theme->code] = $theme;
			}
		} else {
			$this->template->l = $this->getDefaultLayoutData();
			$this->template->t = ['light' => $this->getDefaultThemeData()];
		}
		$this->template->setFile(__DIR__ . '/style.latte');
		$this->getHttpResponse()->setExpiration('1 month');
	}


	private function getDefaultLayoutData(): array
	{
		return [
			'code' => 'cvut',
			'translations' => [],
			'fontSize' => 16,
			'fontFamily' => 'Technika',
			'contentMarginY' => false,
			'imageDisplay' => true,
			'logoWidth' => 100,
			'logoHeight' => 100,
			'primary' => '#0065bd',
			'secondary' => '#9b9b9b',
			'success' => '#a2ad00',
			'danger' => '#c60c30',
			'warning' => '#f0ab00',
			'info' => '#00b2a9',
			'light' => '#e6e6e6',
			'dark' => '#222222',
			'screens' => [
				[
					'code' => 'm',
					'minWidth' => null,
					'maxWidth' => 991.8,
					'layoutWidth' => null,
					'layoutMarginTop' => 0,
					'layoutMarginRight' => 0,
					'layoutMarginBottom' => 0,
					'layoutMarginLeft' => 0,
					'layoutBorderTop' => 0,
					'layoutBorderRight' => 0,
					'layoutBorderBottom' => 0,
					'layoutBorderLeft' => 0,
					'primaryHeight' => 60,
					'primaryWidth' => 245,
					'primaryMarginTop' => 0,
					'primaryMarginRight' => 0,
					'primaryMarginBottom' => 0,
					'primaryMarginLeft' => 0,
					'primaryBorderTop' => 0,
					'primaryBorderRight' => 0,
					'primaryBorderBottom' => 0,
					'primaryBorderLeft' => 0,
					'primaryPaddingTop' => 8,
					'primaryPaddingRight' => 8,
					'primaryPaddingBottom' => 8,
					'primaryPaddingLeft' => 8,
					'secondaryHeight' => 60,
					'secondaryWidth' => 245,
					'secondaryMarginTop' => 0,
					'secondaryMarginRight' => 0,
					'secondaryMarginBottom' => 0,
					'secondaryMarginLeft' => 0,
					'secondaryBorderTop' => 0,
					'secondaryBorderRight' => 0,
					'secondaryBorderBottom' => 0,
					'secondaryBorderLeft' => 0,
					'secondaryPaddingTop' => 8,
					'secondaryPaddingRight' => 8,
					'secondaryPaddingBottom' => 8,
					'secondaryPaddingLeft' => 8,
					'contentWidth' => null,
					'contentMarginTop' => 0,
					'contentMarginRight' => 0,
					'contentMarginBottom' => 0,
					'contentMarginLeft' => 0,
					'contentBorderTop' => 0,
					'contentBorderRight' => 0,
					'contentBorderBottom' => 0,
					'contentBorderLeft' => 0,
					'contentPaddingTop' => 8,
					'contentPaddingRight' => 8,
					'contentPaddingBottom' => 8,
					'contentPaddingLeft' => 38,
					'primaryMenuItemFlexDirection' => true,
					'primaryIconDisplay' => true,
					'primaryIconFontSize' => null,
					'primarySpanDisplay' => true,
					'primarySpanFontSize' => null,
					'secondaryMenuItemFlexDirection' => true,
					'secondaryIconDisplay' => true,
					'secondaryIconFontSize' => null,
					'secondarySpanDisplay' => true,
					'secondarySpanFontSize' => null,
					'primaryOrientation' => 'h',
					'secondaryOrientation' => 'h',
				],
				[
					'code' => 't',
					'minWidth' => 992,
					'maxWidth' => 1419.8,
					'layoutWidth' => null,
					'layoutMarginTop' => 0,
					'layoutMarginRight' => 0,
					'layoutMarginBottom' => 0,
					'layoutMarginLeft' => 0,
					'layoutBorderTop' => 0,
					'layoutBorderRight' => 0,
					'layoutBorderBottom' => 0,
					'layoutBorderLeft' => 0,
					'primaryHeight' => 60,
					'primaryWidth' => 245,
					'primaryMarginTop' => 0,
					'primaryMarginRight' => 0,
					'primaryMarginBottom' => 0,
					'primaryMarginLeft' => 0,
					'primaryBorderTop' => 0,
					'primaryBorderRight' => 0,
					'primaryBorderBottom' => 0,
					'primaryBorderLeft' => 0,
					'primaryPaddingTop' => 8,
					'primaryPaddingRight' => 20,
					'primaryPaddingBottom' => 8,
					'primaryPaddingLeft' => 20,
					'secondaryHeight' => 60,
					'secondaryWidth' => 245,
					'secondaryMarginTop' => 0,
					'secondaryMarginRight' => 0,
					'secondaryMarginBottom' => 0,
					'secondaryMarginLeft' => 0,
					'secondaryBorderTop' => 0,
					'secondaryBorderRight' => 0,
					'secondaryBorderBottom' => 0,
					'secondaryBorderLeft' => 0,
					'secondaryPaddingTop' => 80,
					'secondaryPaddingRight' => 20,
					'secondaryPaddingBottom' => 20,
					'secondaryPaddingLeft' => 20,
					'contentWidth' => null,
					'contentMarginTop' => 0,
					'contentMarginRight' => 0,
					'contentMarginBottom' => 0,
					'contentMarginLeft' => 0,
					'contentBorderTop' => 0,
					'contentBorderRight' => 0,
					'contentBorderBottom' => 0,
					'contentBorderLeft' => 0,
					'contentPaddingTop' => 80,
					'contentPaddingRight' => 20,
					'contentPaddingBottom' => 20,
					'contentPaddingLeft' => 20,
					'primaryMenuItemFlexDirection' => true,
					'primaryIconDisplay' => true,
					'primaryIconFontSize' => null,
					'primarySpanDisplay' => true,
					'primarySpanFontSize' => null,
					'secondaryMenuItemFlexDirection' => false,
					'secondaryIconDisplay' => true,
					'secondaryIconFontSize' => null,
					'secondarySpanDisplay' => true,
					'secondarySpanFontSize' => null,
					'primaryOrientation' => 'h',
					'secondaryOrientation' => 'v',
				],
				[
					'code' => 'd',
					'minWidth' => 1420,
					'maxWidth' => null,
					'layoutWidth' => null,
					'layoutMarginTop' => 0,
					'layoutMarginRight' => 0,
					'layoutMarginBottom' => 0,
					'layoutMarginLeft' => 0,
					'layoutBorderTop' => 0,
					'layoutBorderRight' => 0,
					'layoutBorderBottom' => 0,
					'layoutBorderLeft' => 0,
					'primaryHeight' => 60,
					'primaryWidth' => 245,
					'primaryMarginTop' => 0,
					'primaryMarginRight' => 0,
					'primaryMarginBottom' => 0,
					'primaryMarginLeft' => 0,
					'primaryBorderTop' => 0,
					'primaryBorderRight' => 0,
					'primaryBorderBottom' => 0,
					'primaryBorderLeft' => 0,
					'primaryPaddingTop' => 20,
					'primaryPaddingRight' => 20,
					'primaryPaddingBottom' => 20,
					'primaryPaddingLeft' => 20,
					'secondaryHeight' => 60,
					'secondaryWidth' => 245,
					'secondaryMarginTop' => 0,
					'secondaryMarginRight' => 0,
					'secondaryMarginBottom' => 0,
					'secondaryMarginLeft' => 0,
					'secondaryBorderTop' => 0,
					'secondaryBorderRight' => 0,
					'secondaryBorderBottom' => 0,
					'secondaryBorderLeft' => 0,
					'secondaryPaddingTop' => 80,
					'secondaryPaddingRight' => 20,
					'secondaryPaddingBottom' => 22,
					'secondaryPaddingLeft' => 20,
					'contentWidth' => 920,
					'contentMarginTop' => 0,
					'contentMarginRight' => 0,
					'contentMarginBottom' => 0,
					'contentMarginLeft' => 0,
					'contentBorderTop' => 0,
					'contentBorderRight' => 0,
					'contentBorderBottom' => 0,
					'contentBorderLeft' => 0,
					'contentPaddingTop' => 80,
					'contentPaddingRight' => 30,
					'contentPaddingBottom' => 20,
					'contentPaddingLeft' => 30,
					'primaryMenuItemFlexDirection' => false,
					'primaryIconDisplay' => true,
					'primaryIconFontSize' => null,
					'primarySpanDisplay' => true,
					'primarySpanFontSize' => null,
					'secondaryMenuItemFlexDirection' => false,
					'secondaryIconDisplay' => true,
					'secondaryIconFontSize' => null,
					'secondarySpanDisplay' => true,
					'secondarySpanFontSize' => null,
					'primaryOrientation' => 'v',
					'secondaryOrientation' => 'v',
				],
			],
		];
	}


	public function getDefaultThemeData(): array
	{
		return [
			'code' => 'light',
			'translations' => [
				[
					'language' => 1,
					'title' => 'Světlý režim',
				],
				[
					'language' => 2,
					'title' => 'Light mode',
				],
			],
			'bodyBg' => '#ffffff',
			'color' => '#000000',
			'headingColor' => '#000000',
			'linkColor' => '#0065bd',
			'activeLinkColor' => '#000000',
			'layoutBg' => '#ffffff',
			'layoutBorderColor' => '#ffffff',
			'primaryBg' => '#0065bd',
			'primaryColor' => '#ffffff',
			'primaryBorderColor' => '#ffffff',
			'primaryLinkColor' => '#ffffff',
			'primaryActiveLinkBg' => '#ffffff',
			'primaryActiveLinkColor' => '#0065bd',
			'secondaryBg' => '#e6e6e6',
			'secondaryColor' => '#000000',
			'secondaryBorderColor' => '#ffffff',
			'secondaryLinkColor' => '#0065bd',
			'secondaryActiveLinkBg' => '#ffffff',
			'secondaryActiveLinkColor' => '#0065bd',
			'contentBg' => '#ffffff',
			'contentBorderColor' => '#dddddd',
		];
	}
}
