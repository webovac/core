<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Language;

use App\Model\Language\LanguageData;
use App\Model\LanguageTranslation\LanguageTranslationDataRepository;
use Nette\DI\Attributes\Inject;


trait CoreLanguageDataRepository
{
	#[Inject] public LanguageTranslationDataRepository $languageTranslationDataRepository;


	public function createDataFromConfig(array $config, string $mode): LanguageData
	{
		/** @var LanguageData $data */
		$data = $this->processor->process($this->getSchema($mode), $config);
		foreach ($data->translations as $key => $translationConfig) {
			$translationConfig['translationLanguage'] ??= $key;
			unset($data->translations[$key]);
			$data->translations[$translationConfig['translationLanguage']] = $this->languageTranslationDataRepository->createDataFromConfig($translationConfig, $mode);
		}
		return $data;
	}


	public function findAllPairs(): array
	{
		$return = [];
		foreach ($this->findAll() as $languageData) {
			$return[$languageData->id] = $languageData->shortcut;
		}
		return $return;
	}
}