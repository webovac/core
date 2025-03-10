<?php

declare(strict_types=1);

namespace Webovac\Core\Presenter\Manifest;

use App\Model\DataModel;
use App\Model\Web\WebData;
use App\Model\WebTranslation\WebTranslationData;
use Nette\Application\Attributes\Persistent;
use Nette\Application\UI\Presenter;
use Nette\DI\Attributes\Inject;
use ReflectionException;


/**
 * @property ManifestTemplate $template
 */
class ManifestPresenter extends Presenter
{
	#[Persistent] public string $host;
	#[Persistent] public string $basePath;
	#[Persistent] public string $lang;
	#[Inject] public DataModel $dataModel;
	public ?WebData $webData;
	private ?WebTranslationData $webTranslationData;


	/**
	 * @throws ReflectionException
	 */
	public function actionDefault(): void
	{
		$languageData = $this->dataModel->getLanguageDataByShortcut($this->lang);
		$this->webData = $this->dataModel->getWebDataByHost($this->host, $this->basePath);
		$this->webTranslationData = $this->webData->getCollection('translations')->getByKey($languageData->id) ?? null;
	}


	public function renderDefault(): void
	{
		$this->template->webData = $this->webData;
		$this->template->webTranslationData = $this->webTranslationData;
		$this->template->lang = $this->dataModel->getLanguageData($this->webData->defaultLanguage)->shortcut;
		$this->template->setFile(__DIR__ . '/manifest.latte');
		$this->getHttpResponse()->setExpiration('1 month');
	}
}
