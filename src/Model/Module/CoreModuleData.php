<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Module;

use App\Model\Module\ModuleData;
use App\Model\ModuleTranslation\ModuleTranslationData;
use App\Model\Page\PageData;
use DateTimeInterface;
use Stepapo\Utils\Attribute\ArrayOfType;


trait CoreModuleData
{
	public ?int $id;
	public string $name;
	public int|string $homePage;
	#[ArrayOfType(ModuleTranslationData::class)] /** @var ModuleTranslationData[] */ public array|null $translations;
	/** @var PageData[]|array */ public array|null $pages;
	public ?string $icon;
	public array $tree;
	public int|string|null $createdByPerson;
	public int|string|null $updatedByPerson;
	public ?DateTimeInterface $createdAt;
	public ?DateTimeInterface $updatedAt;


	public static function createFromArray(mixed $config = [], mixed $key = null, bool $skipDefaults = false): static
	{
		$data = parent::createFromArray($config, $key, $skipDefaults);
		$rank = 1;
		foreach ($data->tree as $parentPage => $pages) {
			ModuleData::processTree((array) $pages, $parentPage, $rank++, $data);
		}
		foreach ($data->pages as $pageKey => $pageConfig) {
			if (!ModuleData::checkPage($pageKey, $data)) {
				unset($data->pages[$pageKey]);
				continue;
			}
			$data->pages[$pageKey] = PageData::createFromArray($pageConfig, $pageKey, $skipDefaults);
		}
		return $data;
	}


	private static function checkPage(string $page, ModuleData $data)
	{
		if (isset($data->pages[$page])) {
			$p = $data->pages[$page];
			$relatedPage = $p['targetPage'] ?? ($p['redirectPage'] ?? null);
			if (!$relatedPage) {
				return true;
			}
			return ModuleData::checkPage(str_contains($relatedPage, ':') ? strtok($relatedPage, ':') : $relatedPage, $data);
		}
		return false;
	}


	private static function processTree(array $pages, string $parentPage, int $rank, ModuleData &$data): void
	{
		$r = 1;
		$data->pages[$parentPage]['rank'] = $rank;
		foreach ($pages as $page => $subPages) {
			if (!ModuleData::checkPage($page, $data)) {
				continue;
			}
			$data->pages[$page]['parentPage'] = $parentPage;
			ModuleData::processTree((array) $subPages, $page, $r++, $data);
		}
	}
}
