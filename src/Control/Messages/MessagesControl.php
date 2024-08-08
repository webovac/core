<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Messages;

use Webovac\Core\Control\BaseControl;


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
