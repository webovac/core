<?php

declare(strict_types=1);

namespace Webovac\Core\Presenter\Manifest;

use App\Model\DataModel;
use App\Model\Web\WebData;
use App\Model\WebTranslation\WebTranslationData;
use Nette\Application\Attributes\Persistent;
use Nette\Application\UI\Presenter;
use Nette\DI\Attributes\Inject;


/**
 * @property ManifestTemplate $template
 */
class ManifestPresenter extends Presenter
{
	#[Persistent] public string $host;
	#[Persistent] public string $basePath;
	#[Persistent] public string $lang;

	#[Inject] public DataModel $dataModel;

	private ?WebData $webData;
	private ?WebTranslationData $webTranslationData;


	public function actionDefault(): void
	{
		$languageData = $this->dataModel->getLanguageDataByShortcut($this->lang);
		if (!$languageData) {
			$this->error();
		}
		$this->webData = $this->dataModel->getWebDataByHost($this->host, $this->basePath);
		if (!$this->webData) {
			$this->error();
		}
		$this->webTranslationData = $this->webData->getCollection('translations')->getBy(['language' => $languageData->id]) ?? null;
		if (!$this->webTranslationData) {
			$this->error();
		}
	}


	public function renderDefault(): void
	{
		$this->template->webData = $this->webData;
		$this->template->webTranslationData = $this->webTranslationData;
		$this->template->setFile(__DIR__ . '/manifest.latte');
		$this->getHttpResponse()->setExpiration('1 month');
	}
}
