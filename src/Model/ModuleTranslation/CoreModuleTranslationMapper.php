<?php

declare(strict_types=1);

namespace Webovac\Core\Model\ModuleTranslation;

use App\Model\Index\IndexRepository;
use App\Model\IndexTranslation\IndexTranslationRepository;
use App\Model\ModuleTranslation\ModuleTranslation;


trait CoreModuleTranslationMapper
{
	public function createIndexTranslation(ModuleTranslation $moduleTranslation): void
	{
		$index = $this->getRepository()->getModel()->getRepository(IndexRepository::class)->getBy(['module' => $moduleTranslation->module]);
		$indexTranslation = $this->getRepository()->getModel()->getRepository(IndexTranslationRepository::class)->getBy([
			'index->module' => $moduleTranslation->module,
			'language' => $moduleTranslation->language
		]);
		if (!$indexTranslation) {
			$this->connection->query("
				INSERT INTO public.index_translation (index_id, language_id, document)
				    SELECT
				    	%i AS index_id,
				    	%i AS language_id,
				    	setweight(to_tsvector(%s), 'A')
							|| setweight(to_tsvector(%s, COALESCE(%?s, ' ')), 'B')
							|| setweight(to_tsvector(%s, COALESCE(%?s, ' ')), 'C') AS document;
			",
				$index->id,
				$moduleTranslation->language->id,
				$moduleTranslation->module->name,
				$moduleTranslation->language->name,
				$moduleTranslation->title,
				$moduleTranslation->language->name,
				$moduleTranslation->description,
			);
		} else {
			$this->connection->query("
				UPDATE public.index_translation
				SET document=s.document
				FROM (
					SELECT setweight(to_tsvector(%s), 'A')
							|| setweight(to_tsvector(%s, COALESCE(%s, ' ')), 'B')
							|| setweight(to_tsvector(%s, COALESCE(%s, ' ')), 'C') AS document
				) AS s
				WHERE index_translation.id = %i;
			",
				$moduleTranslation->module->name,
				$moduleTranslation->language->name,
				$moduleTranslation->title,
				$moduleTranslation->language->name,
				$moduleTranslation->description,
				$indexTranslation->id,
			);
		}
	}
}
