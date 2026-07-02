<?php

declare(strict_types=1);

namespace Webovac\Core\Model;



trait HasContentTrait
{
	public function getContent(): ?string
	{
		return $this->content;
	}


	public function setContent(string $content): self
	{
		$this->content = $content;
		return $this;
	}
}
