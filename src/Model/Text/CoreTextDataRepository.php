<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Text;

use App\Model\Text\TextData;
use App\Model\TextTranslation\TextTranslationDataRepository;
use App\Model\Page\PageDataRepository;
use Nette\DI\Attributes\Inject;


trait CoreTextDataRepository
{
	#[Inject] public TextTranslationDataRepository $textTranslationDataRepository;


	public function createDataFromConfig(array $config, string $mode): TextData
	{
		/** @var TextData $data */
		$data = $this->processor->process($this->getSchema($mode), $config);
		foreach ($data->translations as $key => $translationConfig) {
			$translationConfig['language'] ??= $key;
			unset($data->translations[$key]);
			$data->translations[$translationConfig['language']] = $this->textTranslationDataRepository->createDataFromConfig($translationConfig, $mode);
		}
		return $data;
	}
}