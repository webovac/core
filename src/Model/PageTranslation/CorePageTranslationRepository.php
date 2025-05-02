<?php

declare(strict_types=1);

namespace Webovac\Core\Model\PageTranslation;

use App\Model\Language\Language;
use App\Model\Module\Module;
use App\Model\Page\Page;
use App\Model\PageTranslation\PageTranslation;
use App\Model\PageTranslation\PageTranslationData;
use App\Model\Path\Path;
use App\Model\Web\Web;


trait CorePageTranslationRepository
{
	public function getByData(PageTranslationData $data, Page $page): ?PageTranslation
	{
		return $this->getBy(['page' => $page, is_int($data->language) ? 'language->id' : 'language->shortcut' => $data->language]);
	}


	public function rebuildPaths(
		?Web $web = null,
		?Module $module = null,
		?Page $parentPage = null,
		?Language $language = null,
		?string $parentPath = null
	): void
	{
		if ($module) {
			$filter = ['page->module' => $module, 'page->parentPage' => null];
		} elseif ($parentPage) {
			$filter = ['page->parentPage' => $parentPage];
		} else {
			$filter = ['page->web!=' => null, 'page->parentPage' => null];
		}
		if ($language) {
			$filter['language'] = $language;
		}
		$filter = array_merge($filter, ['page->type' => [Page::TYPE_MODULE, Page::TYPE_PAGE]]);
		foreach ($this->findBy($filter) as $pageTranslation) {
			$parts = [];
			if ($parentPath && !$pageTranslation->page->dontInheritPath) {
				$parts[] = $parentPath;
			}
			if ($pageTranslation->path) {
				$parts[] = preg_replace('/<id(.*)>/', "<id[" . $pageTranslation->page->name . "]>", $pageTranslation->path);
			}
			$path = implode('/', $parts);
			$web = $web ?: $pageTranslation->page->web;
			$activePath = $pageTranslation->getActivePath($web);
			if ($web && $pageTranslation->page->type !== Page::TYPE_MODULE && (!$activePath || $activePath->path !== $path)) {
				if ($activePath) {
					if ($this->getModel()->pathRepository->getBy(['path' => $activePath->path, 'id!=' => $activePath->id])) {
						$this->getModel()->remove($activePath);
					} else {
						$activePath->active = false;
						$this->getModel()->persist($activePath);
					}
				}
				$existingPath = $this->getModel()->pathRepository->getBy(['path' => $path, 'active' => false]);
				$newPath = $existingPath ?: new Path;
				$newPath->pageTranslation = $pageTranslation;
				$newPath->web = $web;
				$newPath->path = $path;
				$newPath->active = true;
				$newPath->updatedAt = $existingPath ? new \DateTimeImmutable : null;
				$this->getModel()->persist($newPath);
			}
			$this->rebuildPaths(
				$web,
				$pageTranslation->page->type === Page::TYPE_MODULE ? $pageTranslation->page->targetModule : null,
				$pageTranslation->page,
				$pageTranslation->language,
				$path,
			);
		}
	}
}
