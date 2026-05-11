<?php

declare(strict_types=1);

namespace Webovac\Core\Lib;

use Stepapo\Utils\Service;


class ContentProcessor implements Service
{
	private const array ALLOWED_TAGS = [
		'h1', 'h2', 'h3', 'p', 'b', 'strong', 'i', 'a', 'br', 'hr',
		'ol', 'ul', 'li',
		'table', 'thead', 'tbody', 'th', 'tr', 'td',
		'div', 'figure', 'figcaption', 'colgroup', 'col', 'img', 'span', 'code', 'pre'
	];


	public function editorToContent(string $editor): string
	{
		$content = preg_replace_callback_array([
			'/(&gt;)/' => fn(array $m) => '>',
			'/<span.*?>{\$entity->(.+?)}<\/span>/' => fn(array $m) => "{$entity->$m[1]}",
			'/<p>[^<>]*?(<span.*?>)?{control (.+?)}(<\/span>)?[^<>]*?<\/p>/' => fn(array $m) => "{control $m[2]}",
			'/}&nbsp;/' => fn() => '}',
			'/<figure class="table"( style=".+?")?><table.*?>(.+?)<\/table>(<figcaption>.*?<\/figcaption>)?<\/figure>/' => fn(array $m) => "<figure class=\"table override-padding\"$m[1]><table class=\"table table-bordered\">$m[2]</table>$m[3]</figure>",
		], $editor);
		return strip_tags($content, self::ALLOWED_TAGS);
	}


	public function contentToEditor(string $content): string
	{
		return preg_replace_callback_array([
			'/{\$entity->(.+?)}/' => fn(array $m) => "<span class=\"mention\" data-mention=\"{\$entity->$m[1]}\">{\$entity->$m[1]}</span>",
			'/{control (.+?)}/' => fn(array $m) => "<p><span class=\"mention\" data-mention=\"{control $m[1]}\">{control $m[1]}</span></p>",
			'/<figure class="table override-padding"( style=".+?")><table class="table table-bordered">(.+?)<\/table>(<figcaption>.*?<\/figcaption>)?<\/figure>/' => fn(array $m) => "<figure class=\"table\"$m[1]><table>$m[2]</table>$m[3]</figure>",
		], $content);
	}
}