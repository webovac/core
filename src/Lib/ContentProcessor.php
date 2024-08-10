<?php

declare(strict_types=1);

namespace Webovac\Core\Lib;


class ContentProcessor
{
	private const array ALLOWED_TAGS = [
		'h1', 'h2', 'h3', 'p', 'b', 'i', 'a', 'br',
		'ol', 'ul', 'li',
		'table', 'thead', 'tbody', 'th', 'tr', 'td',
		'div', 'figure', 'img', 'span', 'code', 'pre'
	];


	public function editorToContent(string $editor): string
	{
		$content = preg_replace_callback_array([
			'/(&gt;)/' => fn(array $m) => '>',
			'/<span.*?>{\$entity->(.+?)}<\/span>/' => fn(array $m) => '{$entity->' . $m[1] . '}',
			'/<p><span.*?>{control (.+?)}<\/span><\/p>/' => fn(array $m) => '{control ' . $m[1] . '}',
			'/}&nbsp;/' => fn() => '}',
			'/<figure class="table"><table>(.+)<\/table><\/figure>/' => fn(array $m) => '<figure class="table override-padding"><table class="table table-bordered">' . $m[1] . '</table></figure>',
			'/<a( class=".*?")? href="(.*?)" data-page="(.*?)" data-id="([^"]*)">(.*?)<\/a>/' => function (array $m) {
				$class = $m[1];
				$url = $m[2];
				$page = $m[3];
				$id = $m[4];
				$text = $m[5];
				$href = $url ?: ("{plink '//default', pageName: '" . $page . "'" . ($id ? ", id: '" . $id . "'" : "") . "}");
				return '<a' . $class . ' href="' . $href . '">' . $text . '</a>';
			}
		], $editor);
		return strip_tags($content, self::ALLOWED_TAGS);
	}


	public function contentToEditor(string $content): string
	{
		return preg_replace_callback_array([
			'/{\$entity->(.+?)}/' => fn(array $m) => '<span class="mention" data-mention="{$entity->' . $m[1] . '}">{$entity->' . $m[1] . '}</span>',
			'/{control (.+?)}/' => fn(array $m) => '<p><span class="mention" data-mention="{control ' . $m[1] . '}">{control ' . $m[1] . '}</span></p>',
			'/<figure class="table override-padding"><table class="table table-bordered">(.+)<\/table><\/figure>/' => fn(array $m) => '<figure class="table"><table>' . $m[1] . '</table></figure>',
			'/<a href="(.*?)">(.*?)<\/a>/' => function (array $m) {
				$href = $m[1];
				$text = $m[2];
				if (preg_match("/{plink '\/\/default', pageName: '(.+?)?'(?:, id: '?(.+?)'?)?}/", $href, $matches)) {
					$href = '';
					$pageName = $matches[1];
					$id = $matches[2] ?? '';
				} else {
					$pageName = '';
					$id = '';
				}
				return '<a href="' . $href . '" data-page="' . $pageName . '" data-id="' . $id . '">' . $text . '</a>';
			}
		], $content);
	}
}