<?php

namespace Webovac\Core\Control\MenuItem;


use App\Model\DataModel;
use App\Model\Language\LanguageData;
use App\Model\Page\Page;
use App\Model\Page\PageData;
use App\Model\Web\WebData;
use Nextras\Orm\Entity\IEntity;
use Webovac\Core\Control\BaseControl;

/**
 * @property MenuItemTemplate $template
 */
class MenuItemControl extends BaseControl
{
	public function __construct(
		private PageData $pageData,
		private WebData $webData,
		private LanguageData $languageData,
		private ?IEntity $entity,
		private ?PageData $activePageData,
		private string $context,
		private bool $checkActive,
		private DataModel $dataModel,
	) {}


	public function render(): void
	{
		$this->template->pageData = $this->pageData;
		$this->template->pageTranslationData = $this->pageData->getCollection('translations')->getBy(['language' => $this->languageData->id]);
		$this->template->webData = $this->webData;
		$this->template->languageData = $this->languageData;
		$this->template->entity = $this->entity;
		$this->template->activePageData = $this->activePageData;
		$this->template->context = $this->context;
		$this->template->dataModel = $this->dataModel;
		$this->template->href = $this->getHref();
		$this->template->class = $this->getClass();
		$this->template->tag = $this->pageData->type === Page::TYPE_TEXT ? 'div' : 'a';
		$this->template->iconHasWrapper = $this->context === 'content';
		$this->template->render(__DIR__ . '/menuItem.latte');
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
			Page::TYPE_SIGNAL => $this->presenter->link($p->targetSignal . '!'),
			Page::TYPE_EXTERNAL_LINK => $p->targetUrl,
			Page::TYPE_PAGE => $this->presenter->link('Home:', $p->name, $targetParameter, $targetParentParameter),
			default => null,
		};
	}


	private function getClass(): string
	{
		return match($this->context) {
			'buttons' => 'btn btn-outline-' . ($this->pageData->style ?: 'primary'),
			'content' => 'g-col-6 g-col-lg-4 bg-' . ($this->pageData->style ? ($this->pageData->style . '-subtle') : 'light') .  ' p-3',
			default => 'menu-item' . ($this->pageData->style ? ' btn btn-subtle-' . $this->pageData->style : ''),
		} . (
			($this->pageData->id === $this->presenter->pageData->id)
				|| ($this->checkActive && $this->isActive($this->pageData->id))
				|| ($this->checkActive && $this->pageData->targetPage && $this->isActive($this->pageData->targetPage)) ? ' active' : ''
			);
	}
}
