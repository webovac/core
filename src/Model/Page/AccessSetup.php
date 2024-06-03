<?php

namespace Webovac\Core\Model\Page;

use App\Model\Page\Page;
use App\Model\Person\Person;
use Webovac\Core\Exception\LoginRequiredException;
use Webovac\Core\Exception\MissingPermissionException;
use Webovac\Core\Lib\CmsUser;


class AccessSetup
{
	public string $accessFor;
	public array $authorizedPersons;
	public array $authorizedRoles;


	/**
	 * @throws LoginRequiredException
	 * @throws MissingPermissionException
	 */
	public function checkRequirements(CmsUser $cmsUser): void
	{
		if ($this->accessFor === Page::ACCESS_FOR_ALL) {
			return;
		} elseif ($this->accessFor === Page::ACCESS_FOR_LOGGED && !$cmsUser->isLoggedIn()) {
			throw new LoginRequiredException;
		} elseif ($this->accessFor === Page::ACCESS_FOR_SPECIFIC) {
			if (!$cmsUser->isLoggedIn()) {
				throw new LoginRequiredException;
			}
			if (!$this->isPersonAuthorized($cmsUser->getPerson()) && !$this->isRoleAuthorized($cmsUser->getRoles())) {
				throw new MissingPermissionException;
			}
		} elseif ($this->accessFor === Page::ACCESS_FOR_GUEST && $cmsUser->isLoggedIn()) {
			throw new MissingPermissionException;
		}
	}


	public function isUserAuthorized(CmsUser $cmsUser): bool
	{
		if ($this->accessFor === Page::ACCESS_FOR_LOGGED && !$cmsUser->isLoggedIn()) {
			return false;
		} elseif ($this->accessFor === Page::ACCESS_FOR_SPECIFIC) {
			if (!$cmsUser->isLoggedIn()) {
				return false;
			}
			if (!$this->isPersonAuthorized($cmsUser->getPerson()) && !$this->isRoleAuthorized($cmsUser->getRoles())) {
				return false;
			}
		} elseif ($this->accessFor === Page::ACCESS_FOR_GUEST && $cmsUser->isLoggedIn()) {
			return false;
		}
		return true;
	}


	private function isPersonAuthorized(Person $person): bool
	{
		return in_array($person->id, $this->authorizedPersons, true);
	}


	private function isRoleAuthorized(array $roles): bool
	{
		foreach ($roles as $role) {
			if (in_array($role, $this->authorizedRoles, true)) {
				return true;
			}
		}
		return false;
	}

}