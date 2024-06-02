<?php

declare(strict_types=1);

namespace Webovac\Core\Model\File;

use App\Model\File\File;
use App\Model\Person\Person;
use Nextras\Dbal\Utils\DateTimeImmutable;


/**
 * @property int $id {primary}
 *
 * @property string $identifier
 * @property string|null $modernIdentifier
 * @property string|null $compatibleIdentifier
 * @property string $name
 * @property string $extension
 * @property string $contentType
 * @property string $type {enum self::TYPE_*}
 *
 * @property DateTimeImmutable $createdAt {default now}
 *
 * @property Person|null $createdByPerson {m:1 Person, oneSided=true}
 */
trait CoreFile
{
	public const TYPE_FILE = 'file';
	public const TYPE_IMAGE = 'image';
	public const TYPE_SVG = 'svg';


	public function getDefaultIdentifier(): ?string
	{
		return $this->type === File::TYPE_IMAGE ? $this->modernIdentifier : $this->identifier;
	}


	public function getIconIdentifier(): ?string
	{
		if ($this->type === File::TYPE_FILE) {
			return null;
		}
		return $this->type === File::TYPE_SVG ? $this->compatibleIdentifier : $this->identifier;
	}


	public function getBackgroundIdentifier(): ?string
	{
		if ($this->type === File::TYPE_FILE) {
			return null;
		}
		return $this->modernIdentifier;
	}
}
