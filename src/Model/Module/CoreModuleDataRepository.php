<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Module;

use App\Model\ModuleTranslation\ModuleTranslationDataRepository;
use App\Model\Page\PageDataRepository;
use Nette\DI\Attributes\Inject;


trait CoreModuleDataRepository
{
	#[Inject] public ModuleTranslationDataRepository $moduleTranslationDataRepository;
	#[Inject] public PageDataRepository $pageDataRepository;
}