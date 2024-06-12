<?php

declare(strict_types=1);

namespace Webovac\Core\Control;

use Latte\Attributes\TemplateFilter;
use Nette\Application\UI\Control;
use Nette\Application\UI\Presenter;
use Nette\Security\User;


trait CoreTemplate
{
	public Presenter $presenter;
	public Control $control;
	public User $user;
	public string $baseUrl;
	public string $basePath;
	public array $flashes = [];


	#[TemplateFilter]
	public function replacePlaceholder(string $s): string
	{
		return str_replace(
			'{LOGGED_USER_NAME}',
			$this->loggedPerson?->name ?: '',
			$s
		);
	}
}