<?php

declare(strict_types=1);

namespace Webovac\Core\Control;

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
}