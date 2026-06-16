<?php

declare(strict_types=1);

namespace Webovac\Core\Lib;

use Build\Model\Orm;
use Build\Model\Page\Page;
use Build\Model\Person\Person;
use Nextras\Orm\Collection\ICollection;
use Stepapo\Utils\Service;
use Webovac\Core\HasLinkGroups;
use Webovac\Core\Model\HasTranslations;


class LinkProvider implements Service
{
	/** @param HasLinkGroups[] $hasLinkGroups */
	public function __construct(
		private array $hasLinkGroups,
		private Orm $orm,
	) {}


	public function getLinkGroups(HasTranslations $hasTranslations): array
	{
		$linkGroups = [];
		if (!$hasTranslations instanceof Person) {
			$pages = $this->buildPages($this->getAssocPages($hasTranslations));
			$linkGroups += ['Stránky' => $pages];
		}
		foreach ($this->hasLinkGroups as $hasLinkGroups) {
			$linkGroups += $hasLinkGroups->getLinkGroups();
		}
		return $linkGroups;
	}


	private function getAssocPages(HasTranslations $hasTranslations): array
	{
		$pages = $this->orm->pageRepository->findBy([
			ICollection::AND,
			[ICollection::OR, 'web' => $hasTranslations->web, 'module' => $hasTranslations->web?->modules->toCollection()->fetchPairs('id') ?: $hasTranslations->module],
			['type' => [Page::TYPE_PAGE, Page::TYPE_MODULE]],
			['hasParameter' => false],
		])->orderBy('rank');
		$assocPages = [];
		foreach ($pages as $page) {
			if ($page->parentPage) {
				$assocPages['pages'][$page->parentPage->id][] = $page;
			} elseif ($page->module) {
				$assocPages['modules'][$page->module->id][] = $page;
			} else {
				$assocPages['root'][] = $page;
			}
		}
		return $assocPages;
	}


	private function buildPages(array $assocPages, array &$mentions = [], string $type = 'root', ?int $id = null, int $depth = 0): array
	{
		$source = $id ? ($assocPages[$type][$id] ?? []) : ($assocPages[$type] ?? []);
		/** @var Page $page */
		foreach ($source as $page) {
			if ($page->type === Page::TYPE_PAGE) {
				$mentions[] = [
					'id' => $page->name,
					'href' => "{pageLink $page->name}",
					'label' => ($depth > 0 ? str_repeat("  ", $depth - 1) . "– " : '') . $page->title,
				];
			}
			if ($page->type === Page::TYPE_MODULE) {
				$this->buildPages($assocPages, $mentions, 'modules', $page->targetModule->id, $depth + 1);
			} else {
				$this->buildPages($assocPages, $mentions, 'pages', $page->id, $depth + 1);
			}
		}
		return $mentions;
	}
}