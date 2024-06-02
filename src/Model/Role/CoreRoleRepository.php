<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Role;

use App\Model\Role\Role;
use App\Model\Role\RoleData;


trait CoreRoleRepository
{
	public function getByData(RoleData|string $data): ?Role
	{
		return $this->getBy(['code' => $data instanceof RoleData ? $data->code : $data]);
	}
}
