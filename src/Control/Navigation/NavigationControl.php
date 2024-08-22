<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Navigation;

use App\Model\DataModel;
use App\Model\Language\LanguageData;
use App\Model\Page\PageData;
use App\Model\Web\WebData;
use ReflectionException;
use Webovac\Core\Control\BaseControl;
use Webovac\Core\Lib\ModuleChecker;
use Webovac\Core\Model\CmsEntity;


/**
 * @property NavigationTemplate $template
 */
class NavigationControl extends BaseControl
{
	public function __construct(
		private WebData $webData,
		private ?PageData $pageData,
		private LanguageData $languageData,
		private ?CmsEntity $entity,
		private ?array $entityList,
		private DataModel $dataModel,
		private ModuleChecker $moduleChecker,
	) {}


	/**
	 * @throws ReflectionException
	 */
	public function render(): void
	{
		if (!$this->pageData) {
			return;
		}
		if ($this->entityList && method_exists($this->entity, 'getMenuItems')) {
			$this->template->entityMenuItems = $this->entity->getMenuItems();
		}
		$this->template->pageDatas = $this->dataModel->getChildPageDatas($this->webData, $this->pageData, $this->languageData);
		if ($this->moduleChecker->isModuleInstalled('style')) {
			$this->template->layoutData = $this->dataModel->getLayoutData($this->webData->layout);
		}
		$this->template->activePageData = $this->pageData;
		$this->template->title = $this->entity && $this->pageData->hasParameter
			? $this->entity->getTitle($this->languageData)
			: $this->pageData->getCollection('translations')->getBy(['language' => $this->languageData->id])->title;
		$this->template->webData = $this->webData;
		$this->template->languageData = $this->languageData;
		$this->template->dataModel = $this->dataModel;
		$this->template->entity = $this->entity;
		$this->template->render(__DIR__ . '/navigation.latte');
	}
}
