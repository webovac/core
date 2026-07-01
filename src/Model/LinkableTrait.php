<?php

declare(strict_types=1);

namespace Webovac\Core\Model;

use Nette\Application\UI\Component;
use Nette\InvalidArgumentException;


trait LinkableTrait
{
	public function getLink(Component $component, ?string $context = null): string
	{
		if (!$this instanceof Linkable) {
			throw new InvalidArgumentException("Object of class '" . $this::class . "' does not implement interface '" . Linkable::class . "'.");
		}
		return $component->link('//Home:default', [$this->getPageName($context), $this->getParameters($context)]);
	}


	public function redirectToDetail(Component $component, ?string $context = null): void
	{
		if (!$this instanceof Linkable) {
			throw new InvalidArgumentException("Object of class '" . $this::class . "' does not implement interface '" . Linkable::class . "'.");
		}
		$component->redirect('//Home:default', [$this->getPageName($context), $this->getParameters($context)]);
	}


	public function getParameters(?string $context = null): array
	{
		return [$this->getPageName($context) => $this->{$this->getRepository()->getKeyParameter()}];
	}


	abstract public function getPageName(?string $context = null): string;
}
