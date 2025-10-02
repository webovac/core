<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Role;

use Build\Model\Role\Role;
use Build\Model\Role\RoleData;


trait CoreRoleRepository
{
	public function getByData(RoleData|string $data): ?Role
	{
		return $this->getBy(['code' => $data instanceof RoleData ? $data->code : $data]);
	}


	public function createFromString(string $data): Role
	{
		$role = new Role;
		$role->code = $data;
		$this->persist($role);
		return $role;
	}
}
