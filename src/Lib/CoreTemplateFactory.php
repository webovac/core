<?php

declare(strict_types=1);

namespace Webovac\Core\Lib;

use App\Control\BaseTemplate;
use Nette\Application\UI\Template;
use Nette\DI\Attributes\Inject;
use Stepapo\Dataset\Latte\Filters;


trait CoreTemplateFactory
{
	#[Inject] public Dir $dir;
	#[Inject] public ModuleChecker $moduleChecker;
	public function injectCoreCreate(): void
	{
		$this->onCreate[] = function (Template $template) {
			if ($template instanceof BaseTemplate) {
				$template->addFilter('plural', [Filters::class, 'plural']);
				$template->addFilter('intlDate', [Filters::class, 'intlDate']);
				$template->addFilter('mTime', fn($path) => filemtime($this->dir->getWwwDir() . DIRECTORY_SEPARATOR . $path));
				$template->addFilter('rgb', [$this, 'rgb']);
				$template->addFilter('tint', [$this, 'tint']);
				$template->addFilter('shade', [$this, 'shade']);
				$template->addFunction('isModuleInstalled', fn(string $name) => $this->moduleChecker->isModuleInstalled($name));
				$template->addFunction('core', fn(string $name) => __DIR__ . '/../templates/' . $name . '.latte');
			}
		};
	}


	public static function rgb(string $color): string
	{
		list($r, $g, $b) = self::hex2rgb($color);
		return sprintf("%s, %s, %s", $r, $g, $b);
	}


	public static function mix(array $color1, array $color2, float $weight = 0.5): array
	{
		return array_map(
			fn($x, $y) => round($x + $y),
			array_map(
				fn($x) => (1 - $weight) * $x,
				$color1),
			array_map(
				fn($x) => $weight * $x,
				$color2
			)
		);
	}


	public static function tint(array|string $color, float $weight = 0.5): array|string
	{
		$u = self::mix(
			is_string($color) ? self::hex2rgb($color) : $color,
			[255, 255, 255],
			$weight
		);
		return is_string($color) ? self::rgb2hex($u) : $u;
	}


	public static function shade(array|string $color, float $weight = 0.5): array|string
	{
		$u = self::mix(
			is_string($color) ? self::hex2rgb($color) : $color,
			[0, 0, 0],
			$weight
		);
		return is_string($color) ? self::rgb2hex($u) : $u;
	}


	public static function hex2rgb(string $hex): array
	{
		return array_map(
			fn($x) => hexdec($x),
			str_split(str_replace("#", "", $hex), 2)
		);
	}


	public static function rgb2hex(array $rgb): string
	{
		return "#" . implode("", array_map(
				fn($x) => str_pad(dechex((int) $x), 2, "0", STR_PAD_LEFT),
				$rgb
			));
	}
}
