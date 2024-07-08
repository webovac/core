<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Web;

use Nette\Utils\ArrayHash;


class WebModuleData extends ArrayHash
{
	public string $name;
	public int|string|null $parentPage;
	public int $rank;
}
