<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Module;

use Build\Model\Module\ModuleData;
use Build\Model\ModuleTranslation\ModuleTranslationData;
use Build\Model\Page\PageData;
use DateTimeInterface;
use Stepapo\Utils\Attribute\ArrayOfType;
use Stepapo\Utils\Attribute\DefaultValue;


trait CoreModuleData
{
	public array $tree;


	public static function createFromArray(mixed $config = [], mixed $key = null, bool $skipDefaults = false, mixed $parentKey = null): static
	{
		$data = parent::createFromArray($config, $key, $skipDefaults);
		$rank = 1;
		foreach ($data->tree as $parentPage => $pages) {
			ModuleData::processTree((array) $pages, $parentPage, $rank++, $data);
		}
		return $data;
	}


//	private static function checkPage(string $page, ModuleData $data)
//	{
//		if (isset($data->pages[$page])) {
//			$p = $data->pages[$page];
//			$relatedPage = $p['targetPage'] ?? ($p['redirectPage'] ?? null);
//			if (!$relatedPage) {
//				return true;
//			}
//			return ModuleData::checkPage(str_contains($relatedPage, ':') ? strtok($relatedPage, ':') : $relatedPage, $data);
//		}
//		return false;
//	}


	private static function processTree(array $pages, string $parentPage, int $rank, ModuleData &$data): void
	{
		$r = 1;
		$data->pages[$parentPage]['rank'] = $rank;
		foreach ($pages as $page => $subPages) {
//			if (!ModuleData::checkPage($page, $data)) {
//				continue;
//			}
			$data->pages[$page]['parentPage'] = $parentPage;
			ModuleData::processTree((array) $subPages, $page, $r++, $data);
		}
	}
}
