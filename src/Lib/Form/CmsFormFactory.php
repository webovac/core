<?php

declare(strict_types=1);

namespace Webovac\Core\Lib\Form;

use Nette\Application\UI\Form;
use Stepapo\Utils\Service;
use Webovac\Core\Lib\CmsTranslator;


class CmsFormFactory implements Service
{
	public function __construct(
		private CmsTranslator $translator,
	) {}


	public function create(): Form
	{
		$form = new Form;
		$form->setTranslator($this->translator);
		return $form;
	}
}