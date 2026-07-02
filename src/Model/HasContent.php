<?php

declare(strict_types=1);

namespace Webovac\Core\Model;


interface HasContent extends ICmsEntity
{
	function getContent(): ?string;

	function setContent(string $content): self;
}
