<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Text;

use App\Model\Text\Text;
use App\Model\Text\TextData;
use App\Model\Page\PageRepository;
use App\Model\Person\Person;
use Webovac\Core\Model\CmsDataRepository;


trait CoreTextRepository
{
	public function getByParameter(mixed $parameter): ?Text
	{
		return $this->getBy(['id' => $parameter]);
	}


	public function getByData(TextData|string $data): ?Text
	{
		return $this->getBy(['name' => $data instanceof TextData ? $data->name : $data]);
	}
}
