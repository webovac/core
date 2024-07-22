<?php

declare(strict_types=1);

namespace Webovac\Core\Control\Messages;

use App\Control\BaseTemplate;
use App\Model\DataModel;
use App\Model\Language\LanguageData;
use App\Model\Layout\LayoutData;
use App\Model\Page\PageData;
use App\Model\Web\WebData;
use Stepapo\Utils\Model\Collection;
use Webovac\Core\Model\CmsEntity;


class MessagesTemplate extends BaseTemplate
{
	public array $messages;
}
