<?php

declare(strict_types=1);

namespace Webovac\Core\Presenter;

use Build\Model\DataModel;
use Build\Model\Deploy\DeployData;
use Build\Model\Language\LanguageData;
use Build\Model\Orm;
use Build\Model\Page\PageData;
use Build\Model\PageTranslation\PageTranslation;
use Build\Model\PageTranslation\PageTranslationData;
use Build\Model\Preference\Preference;
use Build\Model\Web\WebData;
use Build\Model\WebTranslation\WebTranslationData;
use Latte\Loaders\StringLoader;
use Latte\Sandbox\SecurityPolicy;
use Nette\Application\Attributes\Parameter;
use Nette\Application\Attributes\Persistent;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Template;
use Nette\Application\UI\TemplateFactory;
use Nette\DI\Attributes\Inject;
use Nette\InvalidStateException;
use Nette\Utils\Arrays;
use Nextras\Dbal\Platforms\Data\Fqn;
use Nextras\Orm\Relationships\IRelationshipCollection;
use stdClass;
use Stepapo\Model\Data\Item;
use Webovac\Core\Control\Core\CoreControl;
use Webovac\Core\Control\Core\ICoreControl;
use Webovac\Core\Exception\LoginRequiredException;
use Webovac\Core\Exception\MissingPermissionException;
use Webovac\Core\Lib\CmsTranslator;
use Webovac\Core\Lib\CmsUser;
use Webovac\Core\Lib\DataProvider;
use Webovac\Core\Lib\Dir;
use Webovac\Core\Lib\FileUploader;
use Webovac\Core\Lib\ModuleChecker;
use Webovac\Core\Lib\PageActivator;
use Webovac\Core\Lib\RegisterOrmEvents;
use Webovac\Core\Model\CmsEntity;
use Webovac\Core\Model\HasRequirements;
use Webovac\Core\Model\HasSlugHistory;


trait CorePresenter
{
	#[Persistent] public string $host;
	#[Persistent] public string $basePath;
	#[Persistent] public string $lang;
	#[Persistent] public string $backlink;
	#[Parameter] public string $pageName;
	#[Parameter] public ?array $id = null;
	#[Parameter] public ?string $path = null;
	#[Inject] public ICoreControl $core;
	#[Inject] public CmsUser $cmsUser;
	#[Inject] public Orm $orm;
	#[Inject] public Dir $dir;
	#[Inject] public DataModel $dataModel;
	#[Inject] public FileUploader $fileUploader;
	#[Inject] public ModuleChecker $moduleChecker;
	#[Inject] public CmsTranslator $translator;
	#[Inject] public TemplateFactory $templateFactory;
	#[Inject] public DataProvider $dataProvider;
	#[Inject] public RegisterOrmEvents $registerOrmEvents;
	#[Inject] public PageActivator $pageActivator;
	public ?WebData $webData;
	private ?WebTranslationData $webTranslationData;
	private ?LanguageData $languageData;
	public ?PageData $pageData;
	protected ?PageTranslation $pageTranslation;
	private ?PageTranslationData $pageTranslationData;
	private ?PageData $navigationPageData;
	private ?PageData $buttonsPageData;
	private ?DeployData $deployData;
	private ?Preference $preference;
	private ?CmsEntity $entity = null;
	/** @var CmsEntity[] */ private ?array $entityList = null;


	public function injectCoreStartup(): void
	{
		$this->onStartup[] = function () {
			$this->registerOrmEvents->register();
			$this->checkLoggedPerson();
			$this->setData();
			$this->checkPageRequirements();
			$this->checkPreferences();
			$this->setEntity();
			$this->activatePages();
		};
	}


	public function injectCoreRender(): void
	{
		$this->onRender[] = function () {
//			bdump($this->orm->pageRepository->findBy(['translations->content:contains' => 'Stránka'])->fetchAll());
//			bdump($this->orm->pageRepository->findBy(['translations->id:count' => 2])->fetchAll());
			$this->template->languageData = $this->languageData;
			$this->template->webData = $this->webData;
			if ($this->webData->iconFile) {
				$this->template->smallIconUrl = $this->fileUploader->getUrl($this->webData->iconFile->getIconIdentifier(), '32x32');
				$this->template->largeIconUrl = $this->fileUploader->getUrl($this->webData->largeIconFile->getIconIdentifier(), '192x192');
			}
			$this->template->webTranslationData = $this->webTranslationData;
			$this->template->pageData = $this->pageData;
			$this->template->deployData = $this->deployData;
			$this->template->imageUrl = $this->getImageUrl();
			$this->template->pageTranslation = $this->pageTranslation;
			$this->template->hasSideMenu = (bool) $this->navigationPageData;
			$this->template->emptyNavigation = $this->getEmptyNavigation();
			$this->template->entity = $this->entity;
			$entityName = $this->getEntityName();
			$this->template->entityName = $entityName;
			$this->template->description = $this->getDescription();
			$this->template->title = $this->getTitle();
			$this->template->metaTitle = $this->getMetaTitle();
			$this->template->metaType = $entityName ?: 'page';
			$this->template->metaUrl = $this->getMetaUrl();
			$this->template->bodyClasses = $this->getBodyClasses();
			$this->render();
		};
	}


	public function createComponentCore(): CoreControl
	{
		return $this->core->create(
			$this->entity,
			$this->entityList,
		);
	}


	public function handleSetLanguage(string $language): void
	{
		$this->orm->preferenceRepository->setPreference(
			webData: $this->webData,
			person: $this->cmsUser->getPerson(),
			data: ['language' => $this->orm->languageRepository->getBy(['shortcut' => $language])],
		);
		$this->redirect('//this');
	}


	private function activatePages(): void
	{
		$parameters = [];
		if (!in_array($this->webData->homePage, $this->pageData->parentPages, true)) {
			array_unshift($this->pageData->parentPages, $this->webData->homePage);
		}
		foreach ($this->pageData->parentPages as $id) {
			$pageData = $this->dataModel->getPageData($this->webData->id, $id);
			$title = $pageData->getCollection('translations')->getByKey($this->languageData->id)->title;
			if ($pageData->hasParameter) {
				if ($this->id) {
					$lastDetailRootPage = $this->dataModel->getPageData($this->webData->id, Arrays::last($pageData->parentDetailRootPages));
					if (!isset($parameters[$lastDetailRootPage->name])) {
						$parameters[$lastDetailRootPage->name] = $this->id[$lastDetailRootPage->name];
					}
					if ($pageData->isDetailRoot) {
						$entity = $this->orm
							->getRepositoryByName($lastDetailRootPage->repository . 'Repository')
							->getByParameters($parameters, null, $this->webData);
						$title = $entity->getTitle();
					}
				} elseif ($this->path) {
					$path = [];
					$title = $this->entity->getTitle();
					foreach ($this->entityList as $entity) {
						if ($entity === $this->entity) {
							continue;
						}
						$path[] = Arrays::first($entity->getParameters());
						$this->pageActivator->addPage($id, $entity->getTitle(), $this->presenter->link(
							'//default',
							[
								'pageName' => $pageData->name,
								'path' => implode('/', $path),
							],
						));
					}
				}
			}
			$this->pageActivator->addPage(
				$id,
				($pageData->isHomePage ? '<i class="fasl fa-fw fa-home"></i> ' : '') . $title,
				$this->presenter->link(
					'//Home:default',
					[
						'pageName' => $pageData->name,
						'id' => $pageData->hasParameter ? $parameters : [],
						'path' => $pageData->hasPath ? $this->path : null,
						'host' => $this->host,
						'basePath' => $this->basePath,
						'lang' => $this->lang,
					],
				),
			);
		}
	}


	private function checkLoggedPerson(): void
	{
		if ($this->cmsUser->isLoggedIn()) {
			if (!$this->cmsUser->getPerson()) {
				$this->cmsUser->logout();
				$this->redirect('this');
			}
		}
	}


	private function setData(): void
	{
		$this->languageData = $this->dataModel->getLanguageDataByShortcut($this->lang);
		$this->webData = $this->dataModel->getWebDataByHost($this->host, $this->basePath);
		if (!$this->webData) {
			$this->error();
		}
		$this->webTranslationData = $this->webData->getCollection('translations')->getByKey($this->languageData->id) ?? null;
		if (!$this->webTranslationData) {
			$this->languageData = $this->dataModel->getLanguageData($this->webData->defaultLanguage);
			$this->lang = $this->languageData->shortcut;
			$this->webTranslationData = $this->webData->getCollection('translations')->getByKey($this->languageData->id) ?? null;
		}
		$this->pageData = $this->dataModel->getPageDataByName($this->webData->id, $this->pageName);
		$this->pageTranslation = $this->orm->pageTranslationRepository->getBy(['page' => $this->pageData->id, 'language' => $this->languageData->id]);
		$this->pageTranslationData = $this->pageData->getCollection('translations')->getByKey($this->languageData->id) ?? null;
		$this->deployData = $this->dataModel->getLastDeployData();
		$this->navigationPageData = $this->pageData->navigationPage ? $this->dataModel->getPageData($this->webData->id, $this->pageData->navigationPage) : null;
		$this->buttonsPageData = $this->pageData->buttonsPage ? $this->dataModel->getPageData($this->webData->id, $this->pageData->buttonsPage) : null;
		$this->translator->setLanguageData($this->languageData);
		$this->templateFactory->onCreate[] = function (Template $template) {
			$template->getLatte()->setLocale($this->languageData->shortcut);
		};
		$this->dataProvider
			->setLanguageData($this->languageData)
			->setWebData($this->webData)
			->setPageData($this->pageData)
			->setNavigationPageData($this->navigationPageData)
			->setButtonsPageData($this->buttonsPageData)
			->setDeployData($this->deployData);
	}


	private function checkPageRequirements(): void
	{
		try {
			$this->pageData->checkRequirements($this->cmsUser, $this->webData);
		} catch (MissingPermissionException $e) {
			throw new ForbiddenRequestException;
		} catch (LoginRequiredException $e) {
			$loginPage = $this->dataModel->getPageDataByName($this->webData->id, 'FsvAuth:Home') ?: $this->dataModel->getPageDataByName($this->webData->id, 'Auth:Home');
			if ($this->webData->disableBacklink) {
				$this->redirect('Home:default', ['pageName' => $loginPage->name, 'host' => $this->host, 'basePath' => $this->basePath, 'lang' => $this->lang]);
			} else {
				$this->redirect('Home:default', ['pageName' => $loginPage->name, 'backlink' => $this->storeRequest()]);
			}
		}
	}


	private function checkPreferences(): void
	{
		if ($this->cmsUser->isLoggedIn()) {
			$this->preference = $this->orm->preferenceRepository->getPreference($this->webData, $this->cmsUser->getPerson());
			if ($this->preference && $this->preference->language) {
				if ($this->lang !== $this->preference->language->shortcut && $this->pageData->getCollection('translations')->getByKey($this->preference->language->id)) {
					$languageData = $this->dataModel->getLanguageData($this->preference->language->id);
					$this->lang = $languageData->shortcut;
					$this->redirect('this');
				}
			}
		}
	}


	private function setEntity(): void
	{
		if ($this->pageData->hasParameter) {
			if (!$this->pageData->parentDetailRootPages) {
				throw new InvalidStateException;
			}
			$lastDetailRootPage = $this->dataModel->getPageData($this->webData->id, Arrays::last($this->pageData->parentDetailRootPages));
			$repository = $this->orm->getRepositoryByName($lastDetailRootPage->repository . 'Repository');
			$this->entity = $repository->getByParameters($this->id, $this->path ?? null, $this->webData);
			if ($this->path) {
				$this->entityList = $repository->getEntityListByPath($this->path);
			}
			if (!$this->entity) {
				$this->error();
			}
			if ($this->entity instanceof HasSlugHistory) {
				$this->entity->checkForRedirect($this->id, $this->pageData, $this->languageData, $this);
			}
			if ($this->entity instanceof HasRequirements && !$this->entity->checkRequirements($this->cmsUser, $this->webData, $this->pageData->authorizingTag)) {
				throw new ForbiddenRequestException;
			}
		}
	}


	private function getTitle(): ?string
	{
		return $this->entity && method_exists($this->entity, 'getTitle')
			? $this->entity->getTitle()
			: $this->pageTranslation->title;
	}


	private function getMetaTitle(): string
	{
		return (!$this->entity || !$this->pageData->isDetailRoot ? $this->pageTranslation->title : '')
			. ($this->entity && !$this->pageData->isDetailRoot ? ' | ' : '' )
			. ($this->entity ? $this->entity->getTitle() : '');
	}


	private function getDescription(): ?string
	{
		return $this->entity && method_exists($this->entity, 'getDescription')
			? $this->entity->getDescription($this->languageData)
			: $this->pageTranslation->description;
	}


	private function getImageUrl(): ?string
	{
		$imageFile = $this->entity && method_exists($this->entity, 'getImageFile')
			? $this->entity->getImageFile()
			: $this->pageData->imageFile;
		return $imageFile
			? $this->fileUploader->getUrl($imageFile->getBackgroundIdentifier(), '1200x630')
			: null;
	}


	private function getEntityName(): ?string
	{
		$tableName = $this->entity?->getRepository()->getMapper()->getTableName();
		return $tableName instanceof Fqn ? $tableName->name : $tableName;
	}


	private function getEmptyNavigation(): bool
	{
		return $this->navigationPageData?->getChildPageDatas($this->dataModel, $this->webData, $this->cmsUser, $this->entity)->count()
			+ ($this->entityList && method_exists($this->entity, 'getMenuItems') ? count($this->entity->getMenuItems()) : null) === 0;
	}


	private function getMetaUrl(): string
	{
		$homePage = $this->dataModel->getPageData($this->webData->id, $this->webData->homePage);
		return $this->request->getPresenterName() === 'Core:Error4xx'
			? $this->link('//Home:default', [
				'pageName' => $homePage->name,
				'host' => $this->host,
				'basePath' => $this->basePath,
				'lang' => $this->lang
			])
			: $this->link('//this');
	}


	private function getBodyClasses(): array
	{
		$classes = [];
		$classes[] = "web-{$this->webData->code}";
		$classes[] = 'layout-' . ($this->moduleChecker->isModuleInstalled('style') ? $this->layoutData->code : 'cvut');
		$classes[] = 'theme-' . ($this->moduleChecker->isModuleInstalled('style') ? $this->themeData->code : 'light');
		if ($this->moduleChecker->isModuleInstalled('style')) {
			foreach ($this->layoutData->screens as $screen) {
				if ($screen->primaryCollapsed) {
					$classes[] = "primary-$screen->code-collapsed";
				}
				if ($screen->secondaryCollapsed) {
					$classes[] = "secondary-$screen->code-collapsed";
				}
			}
		} else {
			$classes[] = "primary-m-collapsed";
			$classes[] = "secondary-m-collapsed";
		}
		return $classes;
	}


	private function render(): void
	{
		if ($this->request->getPresenterName() === 'Core:Error4xx') {
			$file = __DIR__ . "/Error4xx/{$this->getParameter('exception')->getCode()}.latte";
			$main = file_get_contents(is_file($file) ? $file : __DIR__ . '/4xx.latte');
		} else {
			$main = $this->pageTranslation->content ?: '';
		}
		$this->template->getLatte()->setLoader(new StringLoader([
			'@layout.latte' => file_get_contents($this->dir->getAppDir() . "/Presenter/@layout.latte"),
			'layout.latte' => file_get_contents(__DIR__ . "/../templates/layout.latte"),
			'main.file' => $main,
			'footer.file' => $this->webTranslationData->footer ?: '',
		]))
			->setSandboxMode()
			->setPolicy(
				SecurityPolicy::createSafePolicy()
					->allowTags(['include', 'control', 'plink', 'contentType', 'sandbox', 'snippet', 'snippetArea'])
					->allowFilters(['noescape', 'mTime', 'replaceKey', 'translate', 'localDate'])
					->allowProperties(stdClass::class, SecurityPolicy::All)
					->allowProperties(CmsEntity::class, SecurityPolicy::All)
					->allowProperties(Item::class, SecurityPolicy::All)
					->allowMethods(CmsUser::class, SecurityPolicy::All)
					->allowMethods(CmsEntity::class, SecurityPolicy::All)
					->allowMethods(IRelationshipCollection::class, SecurityPolicy::All)
					->allowMethods(Item::class, ['loadAsset'])
					->allowFunctions(['is_numeric', 'max', 'isModuleInstalled', 'lcfirst', 'in_array', 'str_contains', 'core'])
			);
		$this->template->setFile('@layout.latte');
	}
}
