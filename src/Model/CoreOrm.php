<?php

declare(strict_types=1);

namespace Webovac\Core\Model;

use App\Model\File\FileRepository;
use App\Model\Language\LanguageRepository;
use App\Model\LanguageTranslation\LanguageTranslationRepository;
use App\Model\Module\ModuleRepository;
use App\Model\ModuleTranslation\ModuleTranslationRepository;
use App\Model\Page\PageRepository;
use App\Model\PageTranslation\PageTranslationRepository;
use App\Model\Person\PersonRepository;
use App\Model\Preference\PreferenceRepository;
use App\Model\Role\RoleRepository;
use App\Model\Text\TextRepository;
use App\Model\TextTranslation\TextTranslationRepository;
use App\Model\Web\WebRepository;
use App\Model\WebTranslation\WebTranslationRepository;


/**
 * @property-read FileRepository $fileRepository
 * @property-read LanguageRepository $languageRepository
 * @property-read LanguageTranslationRepository $languageTranslationRepository
 * @property-read ModuleRepository $moduleRepository
 * @property-read ModuleTranslationRepository $moduleTranslationRepository
 * @property-read PreferenceRepository $preferenceRepository
 * @property-read PageRepository $pageRepository
 * @property-read PageTranslationRepository $pageTranslationRepository
 * @property-read PersonRepository $personRepository
 * @property-read RoleRepository $roleRepository
 * @property-read TextRepository $textRepository
 * @property-read TextTranslationRepository $textTranslationRepository
 * @property-read WebRepository $webRepository
 * @property-read WebTranslationRepository $webTranslationRepository
 */
trait CoreOrm
{
}
