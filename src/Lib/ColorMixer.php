<?php

declare(strict_types=1);

namespace Webovac\Core\Lib;

use Stepapo\Utils\Service;


class ColorMixer implements Service
{
	public function rgb(string $color): string
	{
		list($r, $g, $b) = $this->hex2rgb($color);
		return sprintf("%s, %s, %s", $r, $g, $b);
	}


	public function tint(array|string $color, float $weight = 0.5): array|string
	{
		$u = $this->mix(
			is_string($color) ? $this->hex2rgb($color) : $color,
			[255, 255, 255],
			$weight,
		);
		return is_string($color) ? $this->rgb2hex($u) : $u;
	}


	public function shade(array|string $color, float $weight = 0.5): array|string
	{
		$u = $this->mix(
			is_string($color) ? $this->hex2rgb($color) : $color,
			[0, 0, 0],
			$weight,
		);
		return is_string($color) ? $this->rgb2hex($u) : $u;
	}


	private function mix(array $color1, array $color2, float $weight = 0.5): array
	{
		return array_map(
			fn($x, $y) => round($x + $y),
			array_map(fn($x) => (1 - $weight) * $x, $color1),
			array_map(fn($x) => $weight * $x, $color2),
		);
	}


	private function hex2rgb(string $hex): array
	{
		return array_map(
			fn($x) => hexdec($x),
			str_split(str_replace("#", "", $hex), 2),
		);
	}


	private function rgb2hex(array $rgb): string
	{
		return "#" . implode("", array_map(
			fn($x) => str_pad(dechex((int) $x), 2, "0", STR_PAD_LEFT),
			$rgb,
		));
	}
}