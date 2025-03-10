<?php

declare(strict_types=1);

namespace Webovac\Core\Model;

use Nette\DI\Attributes\Inject;
use Stepapo\Model\Orm\StepapoEntity;
use Stepapo\Utils\Injectable;
use Webovac\Core\Lib\DataProvider;


abstract class CmsEntity extends StepapoEntity implements Injectable
{
}
