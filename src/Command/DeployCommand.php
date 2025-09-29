<?php

declare(strict_types=1);

namespace Webovac\Core\Command;

use App\Model\Deploy\Deploy;
use App\Model\Orm;
use Stepapo\Utils\Command\Command;


class DeployCommand implements Command
{
	public function __construct(
		private Orm $orm,
	) {}


	public function run(): int
	{
		$deploy = new Deploy;
		$this->orm->persistAndFlush($deploy);
		return 0;
	}
}