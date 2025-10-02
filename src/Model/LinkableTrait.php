<?php

declare(strict_types=1);

namespace Webovac\Core\Model;

use Nette\Application\UI\Component;
use Nette\InvalidArgumentException;


trait LinkableTrait
{
	public function getLink(Component $component): string
	{
		if (!$this instanceof Linkable) {
			throw new InvalidArgumentException("Object of class '" . $this::class . "' does not implement interface '" . Linkable::class . "'.");
		}
		return $component->link('//Home:default', [$this->getPageName(), $this->getParameters()]);
	}


	public function redirectToDetail(Component $component): void
	{
		if (!$this instanceof Linkable) {
			throw new InvalidArgumentException("Object of class '" . $this::class . "' does not implement interface '" . Linkable::class . "'.");
		}
		$component->redirect('//Home:default', [$this->getPageName(), $this->getParameters()]);
	}


	public function getParameters(): array
	{
		return [$this->getPageName() => $this->id];
	}
}