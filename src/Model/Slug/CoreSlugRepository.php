<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Slug;


use App\Model\Web\WebData;
use Nextras\Orm\Collection\ICollection;

trait CoreSlugRepository
{
//	public function getByData(SlugData|string $data): ?Slug
//	{
//		return $this->getBy([
//			'slug' => $data instanceof SlugData ? $data->slug : $data,
//		]);
//	}



	public function getFilterByWeb(WebData $webData): array
	{
		return [
			ICollection::OR,
			'articleTranslation->article->web->id' => $webData->id,
		];
	}
}
