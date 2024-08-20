<?php

declare(strict_types=1);

namespace Webovac\Core\Control\MenuItem;

use App\Model\DataModel;
use App\Model\Language\LanguageData;
use App\Model\Page\Page;
use App\Model\Page\PageData;
use App\Model\PageTranslation\PageTranslationData;
use App\Model\Web\WebData;
use Nette\Application\UI\InvalidLinkException;
use Nette\Utils\Arrays;
use ReflectionException;
use Webovac\Core\Control\BaseControl;
use Webovac\Core\Lib\CmsUser;
use Webovac\Core\Model\CmsEntity;
use Webovac\Core\Model\HasRequirements;

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
		private ?CmsEntity $linkedEntity,
		private DataModel $dataModel,
		private CmsUser $cmsUser,
	) {}


	/**
	 * @throws ReflectionException
	 */
	public function loadState(array $params): void
	{
		parent::loadState($params);
	}


	public function render(): void
	{
		if ($this->entity instanceof HasRequirements && !$this->entity->checkRequirements($this->cmsUser, $this->webData, $this->pageData->authorizingTag)) {
			return;
		}
		$t = $this->pageData->getCollection('translations')->getBy(['language' => $this->languageData->id]);
		$this->pageTranslationData = $t ?: $this->pageData->getCollection('translations')->getBy(['language' => $this->webData->defaultLanguage]);
		$this->targetLanguageData = $t ? $this->languageData : $this->dataModel->languageRepository->getById($this->webData->defaultLanguage);
		$this->template->pageData = $this->pageData;
		$this->template->pageTranslationData = $this->pageTranslationData;
		$this->template->title = $this->linkedEntity && $this->pageData->hasParameter ? $this->linkedEntity->getTitle($this->languageData) : $this->pageTranslationData?->title;
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
		if ($this->linkedEntity && $this->linkedEntity !== $this->entity) {
			return false;
		}
		return $this->presenter->getComponent('core-breadcrumbs')->isActivePage($pageId);
	}


	/**
	 * @throws InvalidLinkException
	 */
	private function getHref(): ?string
	{
		if ($this->pageData->type === Page::TYPE_INTERNAL_LINK && $this->pageData->targetPage) {
			$p = $this->dataModel->getPageData($this->webData->id, $this->pageData->targetPage);
			$targetParameter = $this->pageData->targetParameter;
			$targetPath = $this->pageData->targetPath;
		} else {
			$p = $this->pageData;
			$targetParameter = $p->hasParameter ? $this->entity?->getParameters($this->languageData) : null;
			$targetPath = null;
		}
		$e = $this->linkedEntity ?: $this->entity;
		return match($p->type) {
			Page::TYPE_SIGNAL => $this->presenter->link('//' . $p->targetSignal . '!'),
			Page::TYPE_EXTERNAL_LINK => $p->targetUrl,
			Page::TYPE_PAGE => $this->presenter->link('//default', [
					'pageName' => $p->name,
					'lang' => $this->targetLanguageData->shortcut,
					'id' => $targetParameter ?: ($p->hasParameter && !$this->presenter->getParameter('path') ? $e->getParameters($this->languageData) : []),
					'path' => $targetPath ?: ($this->presenter->getParameter('path') ? $this->presenter->getParameter('path') . '/' . Arrays::first($e->getParameters($this->languageData)) : ''),
				],
			),
			default => null,
		};
	}


	private function getClass(): string
	{
		return match($this->context) {
			'buttons' => 'btn btn-outline-' . ($this->pageData->style ?: 'primary'),
			'signpost' => 'rounded g-col-6 g-col-lg-4 bg-' . ($this->pageData->style ? ($this->pageData->style . '-subtle') : 'light') .  ' p-3',
			default => 'menu-item' . ($this->pageData->style ? ' btn btn-subtle-' . $this->pageData->style : ''),
		} . (($this->pageData->id === $this->presenter->pageData->id && (!$this->linkedEntity || $this->linkedEntity === $this->entity))
		   || ($this->checkActive && $this->isActive($this->pageData->id))
		   || ($this->checkActive && $this->pageData->targetPage && $this->isActive($this->pageData->targetPage)) ? ' active' : '');
	}
}
