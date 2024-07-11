<?php

declare(strict_types=1);

namespace Webovac\Core\Lib;

use App\Model\Orm;
use App\Model\Person\Person;
use Nette\Security\AuthenticationException;
use Nette\Security\IIdentity;
use Nette\Security\User;


class CmsUser
{
	public ?Person $person;


	public function __construct(
		protected User $user,
		protected Orm $orm,
	) {}


	public function getPerson(): ?Person
	{
		if (!isset($this->person)) {
			$this->person = $this->getId() ? $this->orm->personRepository->getById($this->getId()) : null;
		}
		return $this->person;
	}


	public function getId(): int|null|string
	{
		return $this->user->getId();
	}


	public function getRoles(): array
	{
		return $this->user->getRoles();
	}


	public function getIdentity(): ?IIdentity
	{
		return $this->user->getIdentity();
	}


	public function isLoggedIn(): bool
	{
		return $this->user->isLoggedIn();
	}


	/**
	 * @throws AuthenticationException
	 */
	public function login(string $login, string $password): void
	{
		$this->user->login($login, $password);
	}


	public function logout(): void
	{
		$this->user->logout();
	}


	public function clear(): void
	{
		$this->person = null;
	}
}
