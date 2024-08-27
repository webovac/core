<?php

declare(strict_types=1);

namespace Webovac\Core\Control\PageItem;

use App\Model\Language\LanguageData;
use App\Model\Page\Page;
use Webovac\Core\Control\BaseControl;
use Webovac\Core\Lib\DataProvider;


/**
 * @property PageItemTemplate $template
 */
class PageItemControl extends BaseControl
{
	public function __construct(
		private Page $page,
		private string $moduleClass,
		private string $templateName,
		private DataProvider $dataProvider,
	) {}


	public function render(): void
	{
		$this->template->page = $this->page;
		$this->template->languageData = $this->dataProvider->getLanguageData();
		$this->template->renderFile($this->moduleClass, self::class, $this->templateName);
	}
}
