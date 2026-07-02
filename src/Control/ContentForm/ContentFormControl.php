<?php

declare(strict_types=1);

namespace Webovac\Core\Control\ContentForm;

use Build\Model\DataModel;
use Build\Model\Orm;
use Build\Model\Page\Page;
use Nette\Application\Attributes\CrossOrigin;
use Nette\Application\Attributes\Persistent;
use Nette\Application\UI\Form;
use Nette\Application\UI\InvalidLinkException;
use Nette\Forms\Container;
use Nette\Http\Request;
use Nette\Utils\ArrayHash;
use Nette\Utils\Arrays;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Nextras\Dbal\Utils\DateTimeImmutable;
use Nextras\Orm\Relationships\ManyHasMany;
use Nextras\Orm\Relationships\ManyHasOne;
use Nextras\Orm\Relationships\OneHasMany;
use Nextras\Orm\Relationships\OneHasOne;
use Nextras\Orm\Repository\IRepository;
use Stepapo\Model\Orm\Auditable;
use Webovac\Core\Control\BaseControl;
use Webovac\Core\Lib\CmsUser;
use Webovac\Core\Lib\ComponentProvider;
use Webovac\Core\Lib\ContentProcessor;
use Webovac\Core\Lib\FileUploader;
use Webovac\Core\Lib\Form\CmsFormFactory;
use Webovac\Core\Lib\LinkProvider;
use Webovac\Core\Model\HasContent;
use Webovac\Core\Model\HasTranslations;
use function in_array;


/**
 * @property ContentFormTemplate $template
 */
class ContentFormControl extends BaseControl
{
	#[Persistent] public string $lang = 'cs';
	/** @var \Closure[] */ public array $onSave;


	public function __construct(
		private HasTranslations $hasTranslations,
		private CmsFormFactory $formFactory,
		private FileUploader $fileUploader,
		private Orm $orm,
		private Request $request,
		private CmsUser $cmsUser,
		private ContentProcessor $contentProcessor,
		private ComponentProvider $componentProvider,
		private DataModel $dataModel,
		private LinkProvider $linkProvider,
	) {
		$this->onAnchor[] = function() {
			$languageData = $this->dataModel->getLanguageDataByShortcut($this->lang);
			$translation = $this->hasTranslations->getTranslation($languageData);
			assert(!$translation || $translation instanceof HasContent);
			$content = $this->contentProcessor->contentToEditor($translation?->getContent() ?: '');
			$this['form']['content']->setDefaultValue($content);
		};
	}


	/**
	 * @throws JsonException
	 */
	public function render(): void
	{
		$this->template->hasTranslations = $this->hasTranslations;
		$this->template->lang = $this->lang;
		$mentions = [];
		if ($this->hasTranslations instanceof Page) {
			foreach ($this->componentProvider->getComponents() as $component) {
				if ($component['requires']) {
					if (
					    !$this->hasTranslations->hasParameter
					    || $this->hasTranslations->repository !== lcfirst($component['requires'])
					) {
						continue;
					}
				}
				$mentions[] = "{control {$component['name']}}";
			}
			if ($this->hasTranslations->hasParameter) {
				$mentions = array_merge($mentions, $this->buildEntityMentions($this->orm->getRepositoryByName($this->hasTranslations->repository . 'Repository')));
			}
		}
		$this->template->mentions = Json::encode($mentions);
		$this->template->linkGroups = Json::encode($this->linkProvider->getLinkGroups($this->hasTranslations));
		$this->template->render(__DIR__ . '/contentForm.latte');
	}


	public function handleChangeLang(string $lang): void
	{
		$this->lang = $lang;
		$this->redrawControl();
	}


	#[CrossOrigin]
	public function handleUpload(): string
	{
		$upload = $this->request->getFile('upload');
		$this->getPresenter()->sendJson($this->fileUploader->getResponse($upload));
	}


	/**
	 * @throws InvalidLinkException
	 */
	public function createComponentForm(): Form
	{
		$form = $this->formFactory->create();

		$form->addTextArea('content')
			->setHtmlAttribute('data-upload-url', $this->link('//upload!'));

		$form->addSubmit('send', 'Uložit změny');
		$form['send']->onClick[] = [$this, 'formValidate'];
		$form['send']->onClick[] = [$this, 'formSucceeded'];

		return $form;
	}


	public function formValidate(Form $form, ArrayHash $values): void
	{
	}


	public function formSucceeded(Form $form, ArrayHash $values): void
	{
		$now = new DateTimeImmutable;
		if ($this->hasTranslations instanceof Auditable) {
			$this->hasTranslations
				->setUpdatedByPerson($this->cmsUser->getPerson())
				->setUpdatedAt($now);
		}
		$this->orm->persist($this->hasTranslations);
		$languageData = $this->dataModel->getLanguageDataByShortcut($this->lang);
		$translation = $this->hasTranslations->getTranslation($languageData);
		if ($translation) {
			$metadata = $translation->getMetadata();
			assert($translation instanceof HasContent);
			$translation->setContent($this->contentProcessor->editorToContent($form->getUntrustedValues(Container::Array)['content'])); // @phpstan-ignore offsetAccess.nonOffsetAccessible
			if ($translation instanceof Auditable) {
				$translation
					->setUpdatedByPerson($this->cmsUser->getPerson())
					->setUpdatedAt($now);
			}
			$this->orm->persist($translation);
			$this->orm->flush();
		}
		Arrays::invoke($this->onSave, $this, $this->hasTranslations);
	}


	private function buildEntityMentions(
	    IRepository $repository,
	    array &$mentions = [],
	    string $base = '$entity->',
	    int $depth = 1,
	): array
	{
		$properties = $repository->getEntityMetadata()->getProperties();
		foreach ($properties as $property) {
			if (in_array($property->wrapper, [OneHasMany::class, ManyHasMany::class], true)) {
				continue;
			} elseif (in_array($property->wrapper, [OneHasOne::class, ManyHasOne::class], true)) {
				if ($depth > 2) {
					continue;
				}
				$this->buildEntityMentions(
					$this->orm->getRepository($property->relationship->repository),
					$mentions,
					"$base$property->name->",
					$depth + 1,
				);
			} else {
				$mentions[] = "\{$base$property->name\}";
			}
		}
		return $mentions;
	}
}
