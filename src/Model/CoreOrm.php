<?php

declare(strict_types=1);

namespace Webovac\Core\Model;

use Build\Model\Asset\AssetRepository;
use Build\Model\Deploy\DeployRepository;
use Build\Model\File\FileRepository;
use Build\Model\FileTranslation\FileTranslationRepository;
use Build\Model\Language\LanguageRepository;
use Build\Model\LanguageTranslation\LanguageTranslationRepository;
use Build\Model\Lib\LibRepository;
use Build\Model\Module\ModuleRepository;
use Build\Model\ModuleTranslation\ModuleTranslationRepository;
use Build\Model\Page\PageRepository;
use Build\Model\PageTranslation\PageTranslationRepository;
use Build\Model\Parameter\ParameterRepository;
use Build\Model\Path\PathRepository;
use Build\Model\Person\PersonRepository;
use Build\Model\Preference\PreferenceRepository;
use Build\Model\Role\RoleRepository;
use Build\Model\Signal\SignalRepository;
use Build\Model\Slug\SlugRepository;
use Build\Model\Text\TextRepository;
use Build\Model\TextTranslation\TextTranslationRepository;
use Build\Model\Web\WebRepository;
use Build\Model\WebTranslation\WebTranslationRepository;


/**
 * @property-read AssetRepository $assetRepository
 * @property-read DeployRepository $deployRepository
 * @property-read FileRepository $fileRepository
 * @property-read FileTranslationRepository $fileTranslationRepository
 * @property-read LanguageRepository $languageRepository
 * @property-read LanguageTranslationRepository $languageTranslationRepository
 * @property-read LibRepository $libRepository
 * @property-read ModuleRepository $moduleRepository
 * @property-read ModuleTranslationRepository $moduleTranslationRepository
 * @property-read PreferenceRepository $preferenceRepository
 * @property-read PageRepository $pageRepository
 * @property-read PageTranslationRepository $pageTranslationRepository
 * @property-read PathRepository $pathRepository
 * @property-read PersonRepository $personRepository
 * @property-read ParameterRepository $parameterRepository
 * @property-read SignalRepository $signalRepository
 * @property-read SlugRepository $slugRepository
 * @property-read RoleRepository $roleRepository
 * @property-read TextRepository $textRepository
 * @property-read TextTranslationRepository $textTranslationRepository
 * @property-read WebRepository $webRepository
 * @property-read WebTranslationRepository $webTranslationRepository
 */
trait CoreOrm
{
}
