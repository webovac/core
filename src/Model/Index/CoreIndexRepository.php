<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Index;


trait CoreIndexRepository
{
	public function filterByText(string $text)
	{
		return $this->getMapper()->filterByText($text);
	}
}
