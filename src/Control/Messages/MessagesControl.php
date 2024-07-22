<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Messages;

use App\Model\DataModel;
use App\Model\Language\LanguageData;
use App\Model\Page\PageData;
use App\Model\Web\WebData;
use Nette\Application\UI\Multiplier;
use ReflectionException;
use Webovac\Core\Control\BaseControl;
use Webovac\Core\Control\MenuItem\IMenuItemControl;
use Webovac\Core\Control\MenuItem\MenuItemControl;
use Webovac\Core\Lib\Dir;
use Webovac\Core\Lib\FileUploader;
use Webovac\Core\Lib\ModuleChecker;
use Webovac\Core\Model\CmsEntity;


/**
 * @property MessagesTemplate $template
 */
class MessagesControl extends BaseControl
{
	public function render(): void
	{
		$this->template->messages = (array) $this->getPresenter()->getFlashSession()->get($this->getPresenter()->getParameterId('flash'));
		$this->template->render(__DIR__ . '/messages.latte');
	}
}
