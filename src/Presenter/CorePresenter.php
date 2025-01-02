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
use Nette\Application\UI\Template;
use Nette\Application\UI\TemplateFactory;
use Nette\Caching\Cache;
use Nette\Caching\Storage;
use Nette\DI\Attributes\Inject;
use Nette\InvalidStateException;
use Nette\Utils\Arrays;
use Nextras\Dbal\Platforms\Data\Fqn;
use Nextras\Orm\Relationships\IRelationshipCollection;
use ReflectionClass;
use ReflectionException;
use stdClass;
use Stepapo\Model\Data\Item;
use Webovac\Core\Attribute\RequiresEntity;
use Webovac\Core\Command\DefinitionCommand;
use Webovac\Core\Control\Core\CoreControl;
use Webovac\Core\Control\Core\ICoreControl;
use Webovac\Core\Core;
use Webovac\Core\Exception\LoginRequiredException;
use Webovac\Core\Exception\MissingPermissionException;
use Webovac\Core\Lib\CmsTranslator;
use Webovac\Core\Lib\CmsUser;
use Webovac\Core\Lib\DataProvider;
use Webovac\Core\Lib\Dir;
use Webovac\Core\Lib\FileUploader;
use Webovac\Core\Lib\ModuleChecker;
use Webovac\Core\Lib\RegisterOrmEvents;
use Webovac\Core\Model\CmsEntity;
use Webovac\Core\Model\HasRequirements;


trait CorePresenter
{
	#[Persistent] public string $host;
	#[Persistent] public string $basePath;
	#[Persistent] public string $lang;
	#[Persistent] public string $backlink;
	#[Inject] public ICoreControl $core;
	#[Inject] public CmsUser $cmsUser;
	#[Inject] public Orm $orm;
	#[Inject] public Dir $dir;
	#[Inject] public DataModel $dataModel;
	#[Inject] public Storage $storage;
	#[Inject] public FileUploader $fileUploader;
	#[Inject] public ModuleChecker $moduleChecker;
	#[Inject] public Cache $cache;
	#[Inject] public CmsTranslator $translator;
	#[Inject] public TemplateFactory $templateFactory;
	#[Inject] public DataProvider $dataProvider;
	#[Inject] public RegisterOrmEvents $registerOrmEvents;
	public ?WebData $webData;
	private ?WebTranslationData $webTranslationData;
	private ?LanguageData $languageData;
	public ?PageData $pageData;
	protected ?PageTranslation $pageTranslation;
	private ?PageTranslationData $pageTranslationData;
	private ?PageData $navigationPageData;
	private ?PageData $buttonsPageData;
	private ?Preference $preference;
	private ?CmsEntity $entity = null;
	/** @var CmsEntity[] */ private ?array $entityList = null;
	private string $title;
	public array $components = [];


	public function injectCoreStartup(): void
	{
		$this->onStartup[] = function () {
			$this->registerOrmEvents->registerOrmEvents();
			if ($this->cmsUser->isLoggedIn()) {
				if (!$this->cmsUser->getPerson()) {
					$this->cmsUser->logout();
					$this->redirect('this');
				}
			}
			$this->addComponents(Core::getModuleName(), CoreControl::class);
			$this->languageData = $this->dataModel->getLanguageDataByShortcut($this->lang);
			if (!$this->languageData) {
				$this->error();
			}
			$this->dataProvider->setLanguageData($this->languageData);
			$this->translator->setLanguageData($this->languageData);
			$this->templateFactory->onCreate[] = function (Template $template) {
				$template->getLatte()->setLocale($this->languageData->shortcut);
			};
			$this->webData = $this->dataModel->getWebDataByHost($this->host, $this->basePath);
			if (!$this->webData) {
				$this->error();
			}
			$this->dataProvider->setWebData($this->webData);
			$this->webTranslationData = $this->webData->getCollection('translations')->getBy(['language' => $this->languageData->id]) ?? null;
			if (!$this->webTranslationData) {
				$this->error();
			}
			$this->pageData = $this->dataModel->getPageDataByName($this->webData->id, $this->getParameter('pageName') ?: 'Home');
			if (!$this->pageData) {
				$this->error();
			}
			$this->dataProvider->setPageData($this->pageData);
			$this->pageTranslation = $this->orm->pageTranslationRepository->getBy(['page' => $this->pageData->id, 'language' => $this->languageData->id]);
			if (!$this->pageTranslation) {
				$this->error();
			}
			$this->pageTranslationData = $this->pageData->getCollection('translations')->getBy(['language' => $this->languageData->id]) ?? null;
			try {
				$this->pageData->checkRequirements($this->cmsUser);
			} catch (MissingPermissionException $e) {
				throw new ForbiddenRequestException;
			} catch (LoginRequiredException $e) {
				$loginPage = $this->dataModel->getPageDataByName($this->webData->id, 'FsvAuth:Home') ?: $this->dataModel->getPageDataByName($this->webData->id, 'Auth:Home');
				$this->redirect('Home:default', ['pageName' => $loginPage->name, 'backlink' => $this->storeRequest()]);
			}
			if ($this->pageData->hasParameter) {
				if (!$this->pageData->parentDetailRootPages) {
					throw new InvalidStateException;
				}
				$lastDetailRootPage = $this->dataModel->getPageData($this->webData->id, Arrays::last($this->pageData->parentDetailRootPages));
				if (!$lastDetailRootPage) {
					$this->error();
				}
				$repository = $this->orm->getRepositoryByName($lastDetailRootPage->repository . 'Repository');
				$this->entity = $repository->getByParameters($this->getParameter('id'), $this->getParameter('path'), $this->webData);
				if ($this->getParameter('path')) {
					$this->entityList = $repository->getEntityListByPath($this->getParameter('path'));
				}
				if (!$this->entity) {
					$this->error();
				}
				if ($this->entity instanceof HasRequirements && !$this->entity->checkRequirements($this->cmsUser, $this->webData, $this->pageData->authorizingTag)) {
					throw new ForbiddenRequestException;
				}
			}
			if ($this->cmsUser->isLoggedIn()) {
				$this->preference = $this->orm->preferenceRepository->getPreference($this->webData, $this->cmsUser->getPerson());
				if ($this->preference && $this->preference->language) {
					if ($this->lang !== $this->preference->language->shortcut && $this->pageData->getCollection('translations')->getBy(['language' => $this->preference->language->id])) {
						$languageData = $this->dataModel->languageRepository->getById($this->preference->language->id);
						$this->lang = $languageData->shortcut;
					}
				}
			}
			$this->title = $this->entity ? $this->entity->title : $this->pageTranslation->title;
			$this->navigationPageData = $this->pageData->navigationPage ? $this->dataModel->getPageData($this->webData->id, $this->pageData->navigationPage) : null;
			$this->dataProvider->setNavigationPageData($this->navigationPageData);
			$this->buttonsPageData = $this->pageData->buttonsPage ? $this->dataModel->getPageData($this->webData->id, $this->pageData->buttonsPage) : null;
			$this->dataProvider->setButtonsPageData($this->buttonsPageData);
			$this->buildCrumbs();
		};
	}


	public function injectCoreRender(): void
	{
		$this->onRender[] = function () {
			$this->template->getLatte()->setLocale($this->languageData->shortcut);
			$this->template->languageData = $this->languageData;
			$this->template->webData = $this->webData;
			if ($this->webData->iconFile) {
				$this->template->smallIconUrl = $this->fileUploader->getUrl($this->webData->iconFile->getIconIdentifier(), '32x32');
				$this->template->largeIconUrl = $this->fileUploader->getUrl($this->webData->largeIconFile->getIconIdentifier(), '192x192');
			}
			$this->template->webTranslationData = $this->webTranslationData;
			$this->template->pageData = $this->pageData;
			$this->template->imageUrl = $this->getImageUrl();
			$this->template->pageTranslation = $this->pageTranslation;
			$this->template->pageTranslationData = $this->pageTranslationData;
			$this->template->hasSideMenu = (bool) $this->navigationPageData;
			$this->template->entity = $this->entity;
			$entityName = $this->entity?->getRepository()->getMapper()->getTableName() instanceof Fqn ? $this->entity?->getRepository()->getMapper()->getTableName()->name : $this->entity?->getRepository()->getMapper()->getTableName();
			$this->template->entityName = $entityName;
			$this->template->description = $this->getDescription();
			$this->template->title = $this->title;
			$this->template->metaTitle = (!$this->entity || !$this->pageData->isDetailRoot ? $this->pageTranslationData->title : '')
				. ($this->entity && !$this->pageData->isDetailRoot ? ' | ' : '' )
				. ($this->entity ? $this->entity->title : '');
			$this->template->metaType = $entityName ?: 'page';
			$homePage = $this->dataModel->getPageData($this->webData->id, $this->webData->homePage);
			$this->template->metaUrl = $this->request->getPresenterName() === 'Error4xx' ? $this->link('//Home:default', $homePage->name) : $this->link('//this');
			$this->template->bodyClasses = [];
			$this->template->bodyClasses[] = "web-{$this->webData->code}";
			$this->template->bodyClasses[] = 'layout-' . ($this->moduleChecker->isModuleInstalled('style') ? $this->layoutData->code : 'cvut');
			$this->template->bodyClasses[] = 'theme-' . ($this->moduleChecker->isModuleInstalled('style') ? $this->themeData->code : 'light');
			if ($this->moduleChecker->isModuleInstalled('style')) {
				foreach ($this->layoutData->screens as $screen) {
					if ($screen->primaryCollapsed) {
						$this->template->bodyClasses[] = "primary-$screen->code-collapsed";
					}
					if ($screen->secondaryCollapsed) {
						$this->template->bodyClasses[] = "secondary-$screen->code-collapsed";
					}
				}
			} else {
				$this->template->bodyClasses[] = "primary-m-collapsed";
				$this->template->bodyClasses[] = "secondary-m-collapsed";
			}
			if ($this->request->getPresenterName() === 'Error4xx') {
				$file = __DIR__ . "/../Presenter/Error4xx/{$this->getParameter('exception')->getCode()}.latte";
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
						->allowFilters(['noescape', 'mTime', 'translate'])
						->allowProperties(stdClass::class, SecurityPolicy::All)
						->allowProperties(CmsEntity::class, SecurityPolicy::All)
						->allowProperties(Item::class, SecurityPolicy::All)
						->allowMethods(CmsUser::class, SecurityPolicy::All)
						->allowMethods(CmsEntity::class, SecurityPolicy::All)
						->allowMethods(IRelationshipCollection::class, SecurityPolicy::All)
						->allowFunctions(['is_numeric', 'max', 'isModuleInstalled', 'lcfirst', 'in_array', 'str_contains', 'core'])
				);

			$this->template->setFile('@layout.latte');
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
		$this->redirect('this');
	}


	private function addComponents(string $module, string $className): void
	{
		foreach ($this->getComponentList($className) as $key => $value) {
			$this->components[] = [
				'name' => $module . '-' . (is_numeric($key) ? $value : $key),
				'requires' => is_numeric($key) ? null : Arrays::last(explode('\\', $value)),
			];
		}
	}


	/**
	 * @throws ReflectionException
	 */
	private function getComponentList(string $className): array
	{
		$return = [];
		$rf = new ReflectionClass($className);
		foreach ($rf->getMethods() as $method) {
			preg_match('/createComponent(.+)/', $method->getName(), $m);
			if (!isset($m[1])) {
				continue;
			}
			if ($ar = $method->getAttributes(RequiresEntity::class)) {
				$return[lcfirst($m[1])] = $ar[0]->getArguments()[0];
			} else {
				$return[] = lcfirst($m[1]);
			}
		}
		return $return;
	}


	private function buildCrumbs(): void
	{
		$parameters = [];
		foreach ($this->dataModel->getPageData($this->webData->id, $this->pageData->id)->parentPages as $id) {
			$pageData = $this->dataModel->getPageData($this->webData->id, $id);
			$title = $pageData->getCollection('translations')->getBy(['language' => $this->languageData->id])->title;
			if ($pageData->hasParameter) {
				if ($this->getParameter('id')) {
					$lastDetailRootPage = $this->dataModel->getPageData($this->webData->id, Arrays::last($pageData->parentDetailRootPages));
					if (!isset($parameters[$lastDetailRootPage->name])) {
						$parameters[$lastDetailRootPage->name] = $this->getParameter('id')[$lastDetailRootPage->name];
					}
					if ($pageData->isDetailRoot) {
						$entity = $this->orm
							->getRepositoryByName($lastDetailRootPage->repository . 'Repository')
							->getByParameters($parameters, null, $this->webData);
						$title = $entity->title;
					}
				} elseif ($this->getParameter('path')) {
					$path = [];
					$title = $this->entity->title;
					foreach ($this->entityList as $entity) {
						if ($entity === $this->entity) {
							continue;
						}
						$path[] = Arrays::first($entity->getParameters());
						$this->getComponent('core-breadcrumbs')->addCrumb(
							$id,
							$entity->title,
							$this->presenter->link(
								'//default',
								[
									'pageName' => $pageData->name,
									'path' => implode('/', $path),
								],
							),
						);
					}
				}
			}
			$this->getComponent('core-breadcrumbs')->addCrumb(
				$id,
				($pageData->isHomePage ? '<i class="fasl fa-fw fa-home"></i> ' : '') . $title,
				$this->presenter->link(
					'//Home:default',
					[
						'pageName' => $pageData->name,
						'id' => $pageData->hasParameter ? $parameters : [],
						'path' => $pageData->hasPath ? $this->getParameter('path') : null,
					],
				),
			);
		}
	}


	private function getDescription(): ?string
	{
		return $this->entity && method_exists($this->entity, 'getDescription')
			? $this->entity->getDescription($this->languageData)
			: $this->pageTranslationData->description;
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
}
