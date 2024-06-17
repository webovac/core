<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Breadcrumbs;

use App\Model\DataModel;
use App\Model\Language\LanguageData;
use App\Model\Page\PageData;
use App\Model\Web\WebData;
use Webovac\Core\Control\BaseControl;
use Webovac\Core\Model\CmsEntity;


/**
 * @property BreadcrumbsTemplate $template
 */
class BreadcrumbsControl extends BaseControl
{
	private array $crumbs = [];
	private array $activePages = [];


	public function __construct(
		private WebData $webData,
		private PageData $pageData,
		private LanguageData $languageData,
		private ?CmsEntity $entity,
		private ?CmsEntity $parentEntity,
		private DataModel $dataModel,
	) {}


	public function loadState(array $params): void
	{
		parent::loadState($params);

		if ($this->pageData) {
			foreach ($this->dataModel->getPageData($this->webData->id, $this->pageData->id)->parentPages as $id) {
				$this->addActivePage($id);
				$pageData = $this->dataModel->getPageData($this->webData->id, $id);
				$entity = $this->pageData->repository === $pageData->repository ? $this->entity : $this->parentEntity;
				$title = $pageData->hasParameter  && ($pageData->providesNavigation || $pageData->providesButtons)
					? $entity->getTitle($this->languageData)
					: $pageData->getCollection('translations')->getBy(['language' => $this->languageData->id])->title;
				$this->addCrumb(
					($pageData->isHomePage ? '<i class="fasl fa-fw fa-home"></i> ' : '') . $title,
					$this->presenter->link(
						'Home:',
						$pageData->name,
						$pageData->hasParameter ? $entity?->getParameter($this->languageData) : null,
						$pageData->hasParentParameter ? $entity?->getParentParameter($this->languageData) : null
					)
				);
			}
		}
	}


	public function render(): void
	{
		$this->template->crumbs = $this->crumbs;
		$this->template->webData = $this->webData;
		$this->template->render(__DIR__ . '/breadcrumbs.latte');
	}


	public function addCrumb(string $title, $link): void
	{
		$this->crumbs[] = [
			'title' => $title,
			'link' => $link
		];
	}


	public function addActivePage(int $id): void
	{
		$this->activePages[] = $id;
	}


	public function isActivePage(int $pageId): bool
	{
		return in_array($pageId, $this->activePages, true);
	}
}

