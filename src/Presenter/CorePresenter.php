<?php

declare(strict_types=1);

namespace Webovac\Core\Presenter;

use App\Model\DataModel;
use App\Model\Language\LanguageData;
use App\Model\Orm;
use App\Model\Page\PageData;
use App\Model\PageTranslation\PageTranslation;
use App\Model\PageTranslation\PageTranslationData;
use App\Model\Preference\Preference;
use App\Model\Web\WebData;
use App\Model\WebTranslation\WebTranslationData;
use Latte\Loaders\StringLoader;
use Latte\Sandbox\SecurityPolicy;
use Nette\Application\Attributes\Persistent;
use Nette\Application\ForbiddenRequestException;
use Nette\Caching\Cache;
use Nette\Caching\Storage;
use Nette\DI\Attributes\Inject;
use Nette\InvalidStateException;
use Nette\Utils\Arrays;
use Nextras\Orm\Entity\IEntity;
use Nextras\Orm\Relationships\IRelationshipCollection;
use Webovac\Core\Control\Core\CoreControl;
use Webovac\Core\Control\Core\ICoreControl;
use Webovac\Core\Core;
use Webovac\Core\Lib\Dir;
use Webovac\Core\Lib\FileUploader;
use Webovac\Core\Lib\ModuleChecker;
use Webovac\Core\LoginRequiredException;
use Webovac\Core\MissingPermissionException;
use Webovac\Core\Model\CmsData;
use Webovac\Core\Model\HasRequirements;


trait CorePresenter
{
	#[Persistent] public string $host;
	#[Persistent] public string $basePath;
	#[Persistent] public string $lang;
	#[Persistent] public string $backlink;

	#[Inject] public ICoreControl $core;
	#[Inject] public Orm $orm;
	#[Inject] public Dir $dir;
	#[Inject] public DataModel $dataModel;
	#[Inject] public Storage $storage;
	#[Inject] public FileUploader $fileUploader;
	#[Inject] public ModuleChecker $moduleChecker;
	#[Inject] public Cache $cache;

	private ?WebData $webData;
	private ?WebTranslationData $webTranslationData;
	private ?LanguageData $languageData;
	public ?PageData $pageData;
	protected ?PageTranslation $pageTranslation;
	private ?PageTranslationData $pageTranslationData;
	private ?PageData $navigationPageData;
	private ?PageData $buttonsPageData;
	private ?Preference $preference;
	private ?IEntity $entity = null;
	private ?IEntity $parentEntity = null;
	private string $title;
	public array $components = [];


	public function injectCoreStartup(): void
	{
		$this->onStartup[] = function () {
			$this->addComponents(Core::getModuleName(), CoreControl::getComponentList());
			$this->setupCoreOrmEvents();
			$this->languageData = $this->dataModel->getLanguageDataByShortcut($this->lang);
			if (!$this->languageData) {
				$this->error();
			}
			$this->webData = $this->dataModel->getWebDataByHost($this->host, $this->basePath);
			if (!$this->webData) {
				$this->error();
			}
			$this->webTranslationData = $this->webData->getCollection('translations')->getBy(['language' => $this->languageData->id]) ?? null;
			if (!$this->webTranslationData) {
				$this->error();
			}
			$this->pageData = $this->dataModel->getPageDataByName($this->webData->id, $this->getParameter('pageName') ?: 'Home');
			if (!$this->pageData) {
				$this->error();
			}
			$this->pageTranslation = $this->orm->pageTranslationRepository->getBy(['page' => $this->pageData->id, 'language' => $this->languageData->id]);
			if (!$this->pageTranslation) {
				$this->error();
			}
			$this->pageTranslationData = $this->pageData->getCollection('translations')->getBy(['language' => $this->languageData->id]) ?? null;
			try {
				$this->pageData->checkRequirements($this->cmsUser);
			} catch (MissingPermissionException $e) {
				throw new ForbiddenRequestException();
			} catch (LoginRequiredException $e) {
				$loginPageName = $this->orm->webRepository->getById($this->webData->id)->modules->has($this->orm->moduleRepository->getBy(['name' => 'FsvAuth']))
					? 'FsvAuth:Home'
					: 'Auth:Home';
				$this->redirect('Home:', ['pageName' => $loginPageName, 'backlink' => $this->storeRequest()]);
			}
			if ($id = $this->getParameter('id')) {
				if (!$this->pageData->repository) {
					throw new InvalidStateException();
				}
				if ($parentId = $this->getParameter('parentId')) {
					$this->entity = $this->orm
						->getRepositoryByName($this->pageData->repository . 'Repository')
						->getByParameters($id, $parentId, $this->pageData->parentRepository);
					$this->parentEntity = $this->orm
						->getRepositoryByName($this->pageData->parentRepository . 'Repository')
						->getByParameter($parentId);
					if (!$this->parentEntity) {
						$this->error();
					}
					if ($this->parentEntity instanceof HasRequirements && !$this->parentEntity->checkRequirements($this->cmsUser, $this->pageData->authorizingParentTag)) {
						throw new ForbiddenRequestException();
					}
				} else {
					$this->entity = $this->orm
						->getRepositoryByName($this->pageTranslation->page->repository . 'Repository')
						->getByParameter($id);
				}
				if (!$this->entity) {
					$this->error();
				}
				if ($this->entity instanceof HasRequirements && !$this->entity->checkRequirements($this->cmsUser, $this->pageData->authorizingTag)) {
					throw new ForbiddenRequestException();
				}
			}
			if ($this->cmsUser->isLoggedIn()) {
				if (!$this->cmsUser->getPerson()) {
					$this->cmsUser->logout();
					$this->redirect('this');
				}
				$this->preference = $this->orm->preferenceRepository->getPreference($this->webData, $this->cmsUser->getPerson());
				if ($this->preference && $this->preference->language) {
					if ($this->lang !== $this->preference->language->shortcut) {
						$this->redirect(
							'Home:',
							[
								'pageName' => $this->pageData->name,
								'id' => $this->entity?->getParameter($this->preference->language),
								'parentId' => $this->entity?->getParentParameter($this->preference->language),
								'lang' => $this->preference->language->shortcut,
							]
						);
					}
				}
			}
			$this->title = $this->entity ? $this->entity->getTitle($this->languageData) : $this->pageTranslation->title;
			$this->navigationPageData = $this->pageData->navigationPage ? $this->dataModel->getPageData($this->webData->id, $this->pageData->navigationPage) : null;
			$this->buttonsPageData = $this->pageData->buttonsPage ? $this->dataModel->getPageData($this->webData->id, $this->pageData->buttonsPage) : null;
		};
	}


	public function injectCoreRender(): void
	{
		$this->onRender[] = function () {
			$this->template->languageData = $this->languageData;
			$this->template->webData = $this->webData;
			if ($this->webData->iconFile) {
				$this->template->smallIconUrl = $this->fileUploader->getUrl($this->webData->iconFile->getIconIdentifier(), '32x32');
				$this->template->largeIconUrl = $this->fileUploader->getUrl($this->webData->largeIconFile->getIconIdentifier(), '192x192');
			}
			$this->template->webTranslationData = $this->webTranslationData;
			$this->template->pageData = $this->pageData;
			if ($this->pageData->imageFile) {
				$this->template->imageUrl = $this->fileUploader->getUrl($this->pageData->imageFile->getBackgroundIdentifier(), '1200x630');
			}
			$this->template->pageTranslation = $this->pageTranslation;
			$this->template->pageTranslationData = $this->pageTranslationData;
			$this->template->hasSideMenu = (bool) $this->navigationPageData;
			$this->template->entity = $this->entity;
			$this->template->entityName = $this->entity?->getRepository()->getMapper()->getTableName();
			$this->template->title = $this->title;
			$this->template->metaTitle = (!$this->entity || (!$this->pageData->providesNavigation && !$this->pageData->providesButtons) ? $this->pageTranslationData->title : '')
				. ($this->entity && !$this->pageData->providesNavigation && !$this->pageData->providesButtons ? ' | ' : '' )
				. ($this->entity ? $this->entity->getTitle($this->languageData) : '');
			$this->template->metaType = $this->entity?->getRepository()->getMapper()->getTableName() ?: 'page';
			$this->template->metaUrl = $this->link('//Home:', $this->pageData->name, $this->entity?->getParameter(), $this->entity?->getParentParameter());
			$this->template->webDatas = $this->dataModel->getWebDatas();
			$this->template->showAdmin = $this->dataModel->getPageDataByName($this->webData->id, 'Admin:Home')?->isUserAuthorized($this->cmsUser) ?: false;
			$this->template->bodyClasses = [];
			$this->template->bodyClasses[] = "web-{$this->webData->code}";
			$this->template->bodyClasses[] = 'layout-' . ($this->moduleChecker->isModuleInstalled('style') ? $this->layoutData->code : 'cvut');
			$this->template->bodyClasses[] = 'theme-' . ($this->moduleChecker->isModuleInstalled('style') ? $this->themeData->code : 'light');
			if ($this->moduleChecker->isModuleInstalled('style')) {
				foreach ($this->layoutData->devices as $device) {
					if ($device->primaryCollapsed) {
						$this->template->bodyClasses[] = "primary-{$device->code}-collapsed";
					}
					if ($device->secondaryCollapsed) {
						$this->template->bodyClasses[] = "secondary-{$device->code}-collapsed";
					}
				}
			} else {
				$this->template->bodyClasses[] = "primary-m-collapsed";
				$this->template->bodyClasses[] = "secondary-m-collapsed";
			}
			$this->template->getLatte()->setLoader(new StringLoader([
				'@layout.file' => file_get_contents($this->dir->getAppDir() . "/Presenter/@layout.latte"),
				'main.file' => $this->pageTranslation->content ?: '',
				'footer.file' => $this->webTranslationData->footer ?: '',
			]))
				->setSandboxMode()
				->setPolicy(
					SecurityPolicy::createSafePolicy()
						->allowTags(['include', 'control', 'plink', 'contentType'])
						->allowFilters(['noescape', 'mTime'])
						->allowProperties(\stdClass::class, SecurityPolicy::All)
						->allowProperties(IEntity::class, SecurityPolicy::All)
						->allowProperties(CmsData::class, SecurityPolicy::All)
						->allowMethods(IEntity::class, SecurityPolicy::All)
						->allowMethods(IRelationshipCollection::class, SecurityPolicy::All)
						->allowFunctions(['is_numeric', 'max', 'isModuleInstalled', 'lcfirst'])
				);

			$this->template->setFile('@layout.file');
		};
	}


	public function createComponentCore(): CoreControl
	{
		return $this->core->create(
			$this->webData,
			$this->languageData,
			$this->pageData,
			$this->navigationPageData,
			$this->buttonsPageData,
			$this->entity,
			$this->parentEntity
		);
	}


	public function handleSetLanguage(string $language)
	{
		$this->orm->preferenceRepository->setPreference(
			webData: $this->webData,
			person: $this->cmsUser->getPerson(),
			data: ['language' => $this->orm->languageRepository->getBy(['shortcut' => $language])],
		);
		$this->redirect('this');
	}


	private function addComponents(string $module, array $components): void
	{
		foreach ($components as $key => $value) {
			$this->components[] = [
				'name' => $module . '-' . (is_numeric($key) ? $value : $key),
				'requires' => is_numeric($key) ? null : Arrays::last(explode('\\', $value)),
			];
		}
	}


	private function setupCoreOrmEvents(): void
	{
		$cache = new Cache($this->storage);
		foreach (['onAfterPersist', 'onAfterRemove'] as $property) {
			$this->orm->languageRepository->$property[] = fn() => $cache->remove('language');
			$this->orm->languageTranslationRepository->$property[] = fn() => $cache->remove('language');
			$this->orm->moduleRepository->$property[] = fn() => $cache->remove('page');
			$this->orm->moduleTranslationRepository->$property[] = fn() => $cache->remove('page');
			$this->orm->pageRepository->$property[] = fn() => $cache->remove('page');
			$this->orm->pageTranslationRepository->$property[] = fn() => $cache->remove('page');
			$this->orm->webRepository->$property[] = fn() => $cache->remove('web');
			$this->orm->webTranslationRepository->$property[] = fn() => $cache->remove('web');
		}
	}
}
