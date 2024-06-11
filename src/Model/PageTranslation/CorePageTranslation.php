<?php

declare(strict_types=1);

namespace Webovac\Core\Model\PageTranslation;

use App\Model\Language\Language;
use App\Model\Page\Page;
use App\Model\Page\PageRepository;
use App\Model\Person\Person;
use App\Model\Web\Web;
use Nextras\Dbal\Utils\DateTimeImmutable;


/**
 * @property int $id {primary}
 *
 * @property string|null $path
 * @property string|null $title
 * @property string|null $description
 * @property string|null $onclick
 * @property string|null $content
 *
 * @property DateTimeImmutable $createdAt {default now}
 * @property DateTimeImmutable|null $updatedAt
 *
 * @property Page $page {m:1 Page::$translations}
 * @property Language $language {m:1 Language, oneSided=true}
 * @property Person|null $createdByPerson {m:1 Person, oneSided=true}
 * @property Person|null $updatedByPerson {m:1 Person, oneSided=true}
 */
trait CorePageTranslation
{
	public function getRouteMask(?Web $web = null, array $parts = []): string
	{
		$web ??= $this->page->web;
		return '//'
			. $web->host
			. ($web->basePath ? ('/' . $web->basePath) : '')
			. ($parts ? '/' . implode('/', $parts) : '');
	}


	public function getRouteMetadata(?Web $web = null): array
	{
		$web ??= $this->page->web;
		return [
			'presenter' => 'Home',
			'action' => 'default',
			'host' => $web->host,
			'basePath' => $web->basePath,
			'pageName' => $this->page->name,
			'lang' => $this->language->shortcut,
		];
	}


	public function onAfterPersist(): void
	{
		parent::onAfterPersist();
		$this->getRepository()->getMapper()->createIndexTranslation($this);
	}
}
