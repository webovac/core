<?php

declare(strict_types=1);

namespace Webovac\Core\Model\WebTranslation;

use App\Model\Index\IndexRepository;
use App\Model\IndexTranslation\IndexTranslationRepository;
use App\Model\WebTranslation\WebTranslation;


trait CoreWebTranslationMapper
{
	public function createIndexTranslation(WebTranslation $webTranslation): void
	{
		$index = $this->getRepository()->getModel()->getRepository(IndexRepository::class)->getBy(['web' => $webTranslation->web]);
		$indexTranslation = $this->getRepository()->getModel()->getRepository(IndexTranslationRepository::class)->getBy([
			'index->web' => $webTranslation->web,
			'language' => $webTranslation->language
		]);
		if (!$indexTranslation) {
			$this->connection->query("
				INSERT INTO public.index_translation (index_id, language_id, document)
				    SELECT
				    	%i AS index_id,
				    	%i AS language_id,
				    	setweight(to_tsvector(%s), 'A')
							|| setweight(to_tsvector(%s, COALESCE(%?s, ' ')), 'B')AS document;
			",
				$index->id,
				$webTranslation->language->id,
				$webTranslation->web->code,
				$webTranslation->language->name,
				$webTranslation->title,
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
				$webTranslation->web->code,
				$webTranslation->language->name,
				$webTranslation->title,
				$indexTranslation->id,
			);
		}
	}
}
