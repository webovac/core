<?php

declare(strict_types=1);

namespace Webovac\Core\Model\LanguageTranslation;

use App\Model\Index\IndexRepository;
use App\Model\IndexTranslation\IndexTranslationRepository;
use App\Model\LanguageTranslation\LanguageTranslation;


trait CoreLanguageTranslationMapper
{
	public function createIndexTranslation(LanguageTranslation $languageTranslation): void
	{
		$index = $this->getRepository()->getModel()->getRepository(IndexRepository::class)->getBy(['language' => $languageTranslation->language]);
		$indexTranslation = $this->getRepository()->getModel()->getRepository(IndexTranslationRepository::class)->getBy([
			'index->language' => $languageTranslation->language,
			'language' => $languageTranslation->translationLanguage
		]);
		if (!$indexTranslation) {
			$this->connection->query("
				INSERT INTO public.index_translation (index_id, language_id, document)
				    SELECT
				    	%i AS index_id,
				    	%i AS language_id,
				    	setweight(to_tsvector(%s), 'A')
							|| setweight(to_tsvector(%s, COALESCE(%?s, ' ')), 'B') AS document;
			",
				$index->id,
				$languageTranslation->translationLanguage->id,
				$languageTranslation->language->name,
				$languageTranslation->translationLanguage->name,
				$languageTranslation->title,
			);
		} else {
			$this->connection->query("
				UPDATE public.index_translation
				SET document=s.document
				FROM (
					SELECT setweight(to_tsvector(%s), 'A')
							|| setweight(to_tsvector(%s, COALESCE(%s, ' ')), 'B') AS document
				) AS s
				WHERE index_translation.id = %i;
			",
				$languageTranslation->language->name,
				$languageTranslation->translationLanguage->name,
				$languageTranslation->title,
				$indexTranslation->id,
			);
		}
	}
}
