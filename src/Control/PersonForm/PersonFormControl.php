<?php

declare(strict_types=1);

namespace Webovac\Core\Control\PersonForm;

use Build\Model\Orm;
use Build\Model\Person\Person;
use Build\Model\Person\PersonData;
use Build\Model\PersonTranslation\PersonTranslationData;
use Nette\Application\UI\Form;
use Nette\Forms\Container;
use Nette\Utils\Arrays;
use Nextras\Dbal\Connection;
use Nextras\Dbal\Utils\DateTimeImmutable;
use Webovac\Core\Control\BaseControl;
use Webovac\Core\Lib\CmsUser;
use Webovac\Core\Lib\Form\CmsFormFactory;


/**
 * @property PersonFormTemplate $template
 */
class PersonFormControl extends BaseControl
{
	/** @var \Closure[] */ public array $onSave;


	public function __construct(
		private Person $person,
		private CmsFormFactory $formFactory,
		private Orm $orm,
		private CmsUser $cmsUser,
		private Connection $dbal,
	) {
		$this->onAnchor[] = function() {
			if ($this->person) {
				$data = $this->person->getData();
				$this['form']->setDefaults($data);
			}
		};
	}


	public function render(): void
	{
		$this->template->person = $this->person;
		$this->template->render(__DIR__ . '/personForm.latte');
	}


	public function createComponentForm(): Form
	{
		$form = $this->formFactory->create();
		$languages = $this->orm->languageRepository->findAll()->orderBy('rank')->fetchPairs('id', 'shortcut');
		$translations = $form->addMultiplier('translations', function (Container $translation) use ($form, $languages) {
			$translation->setMappedType(PersonTranslationData::class);
			$translation->addSelect('language', 'Jazyk', $languages)
				->setRequired('Vyberte ze seznamu %label');
			$translation->addText('title', 'O mně')
				->setNullable();
		}, 0);
		$translations->addCreateButton('Přidat překlad')
			->addClass('ajax btn btn-sm btn-secondary')
			->setNoValidate()
			->addOnCreateCallback(fn() => $this->redrawControl());
		$translations->addRemoveButton('x')
			->addClass('ajax btn btn-sm btn-secondary')
			->addOnCreateCallback(fn() => $this->redrawControl());

		$form->addSubmit('send', 'Uložit změny');
		$form['send']->onClick[] = [$this, 'formValidate'];
		$form['send']->onClick[] = [$this, 'formSucceeded'];

		return $form;
	}


	public function formValidate(Form $form, PersonData $data): void
	{
		$languageIds = [];
		foreach ($data->translations as $t) {
			if (in_array($t->language, $languageIds, true)) {
				$form->addError('Nemůžete zadat dvakrát stejný jazyk.');
				return;
			}
			$languageIds[] = $t->language;
		}
	}


	public function formSucceeded(Form $form, PersonData $data): void
	{
		$person = $this->orm->personRepository->createFromData(
			data: $data,
			original: $this->person,
			person: $this->cmsUser->getPerson(),
			date: new DateTimeImmutable,
		);
		$this->orm->flush();
		Arrays::invoke($this->onSave, $this, $person);
	}
}
