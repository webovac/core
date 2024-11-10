<?php

declare(strict_types=1);

namespace Webovac\Core\Lib;

use Stepapo\Utils\Service;


class Dir implements Service
{
	public function __construct(private string $rootDir)
	{}


	public function getRootDir(): string
	{
		return $this->rootDir;
	}


	public function getAppDir(): string
	{
		return $this->rootDir . '/app';
	}


	public function getWwwDir(): string
	{
		return $this->rootDir . '/www';
	}


	public function getTempDir(): string
	{
		return $this->rootDir . '/temp';
	}
}

