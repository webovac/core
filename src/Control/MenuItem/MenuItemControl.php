<?php

declare(strict_types=1);

namespace Webovac\Core\Control\MenuItem;


use App\Model\DataModel;
use App\Model\Language\LanguageData;
use App\Model\Page\Page;
use App\Model\Page\PageData;
use App\Model\PageTranslation\PageTranslationData;
use App\Model\Web\WebData;
use Webovac\Core\Control\BaseControl;
use Webovac\Core\Model\CmsEntity;

/**
 * @property MenuItemTemplate $template
 */
class MenuItemControl extends BaseControl
{
	private ?PageTranslationData $pageTranslationData;
	private ?LanguageData $targetLanguageData;


	public function __construct(
		private PageData $pageData,
		private WebData $webData,
		private LanguageData $languageData,
		private ?CmsEntity $entity,
		private string $context,
		private bool $checkActive,
		private string $moduleClass,
		private string $templateName,
		private DataModel $dataModel,
	) {}


	public function loadState(array $params): void
	{
		parent::loadState($params);
		$t = $this->pageData->getCollection('translations')->getBy(['language' => $this->languageData->id]);
		$this->pageTranslationData = $t ?: $this->pageData->getCollection('translations')->getBy(['language' => $this->webData->defaultLanguage]);
		$this->targetLanguageData = $t ? $this->languageData : $this->dataModel->languageRepository->getById($this->webData->defaultLanguage);
	}


	public function render(): void
	{
		$this->template->pageData = $this->pageData;
		$this->template->pageTranslationData = $this->pageTranslationData;
		$this->template->targetLanguageData = $this->targetLanguageData;
		$this->template->languageData = $this->languageData;
		$this->template->context = $this->context;
		$this->template->href = $this->getHref();
		$this->template->class = $this->getClass();
		$this->template->tag = $this->pageData->type === Page::TYPE_TEXT ? 'div' : 'a';
		$this->template->iconHasWrapper = $this->context === 'signpost';
		$this->template->iconStyle = $this->dataModel->layoutRepository->getById($this->webData->layout)->{$this->context . 'Icon'};
		$this->template->renderFile($this->moduleClass, self::class, $this->templateName);
	}


	public function isActive(int $pageId)
	{
		return $this->presenter->getComponent('core-breadcrumbs')->isActivePage($pageId);
	}


	private function getHref(): ?string
	{
		if ($this->pageData->type === Page::TYPE_INTERNAL_LINK && $this->pageData->targetPage) {
			$p = $this->dataModel->getPageData($this->webData->id, $this->pageData->targetPage);
			$targetParameter = $this->pageData->targetParameter;
			$targetParentParameter = $this->pageData->targetParentParameter;
		} else {
			$p = $this->pageData;
			$targetParameter = $p->hasParameter ? $this->entity?->getParameter($this->languageData) : null;
			$targetParentParameter = $p->hasParentParameter ? $this->entity?->getParentParameter($this->languageData) : null;
		}
		return match($p->type) {
			Page::TYPE_SIGNAL => $this->presenter->link('//' . $p->targetSignal . '!'),
			Page::TYPE_EXTERNAL_LINK => $p->targetUrl,
			Page::TYPE_PAGE => $this->presenter->link('//Home:', [$p->name, $targetParameter, $targetParentParameter, 'lang' => $this->targetLanguageData->shortcut]),
			default => null,
		};
	}


	private function getClass(): string
	{
		return match($this->context) {
			'buttons' => 'btn btn-outline-' . ($this->pageData->style ?: 'primary'),
			'signpost' => 'g-col-6 g-col-lg-4 bg-' . ($this->pageData->style ? ($this->pageData->style . '-subtle') : 'light') .  ' p-3',
			default => 'menu-item' . ($this->pageData->style ? ' btn btn-subtle-' . $this->pageData->style : ''),
		} . (
			($this->pageData->id === $this->presenter->pageData->id)
				|| ($this->checkActive && $this->isActive($this->pageData->id))
				|| ($this->checkActive && $this->pageData->targetPage && $this->isActive($this->pageData->targetPage)) ? ' active' : ''
			);
	}
}
