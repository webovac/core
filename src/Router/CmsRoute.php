<?php

namespace Webovac\Core\Router;

use Nette\Http\IRequest;
use Nette\Http\UrlScript;
use Nette\InvalidArgumentException;
use Nette\Routing\Route;
use Nette\Utils\Strings;


class CmsRoute extends Route
{
	/** key used in metadata */
	private const
		Default = 'defOut',
		Fixity = 'fixity',
		FilterTableOut = 'filterTO';

	/** url type */
	private const
		Host = 1,
		Path = 2,
		Relative = 3;

	/** fixity types - has default value and is: */
	private const
		InQuery = 0,
		InPath = 1, // in brackets is default value = null
		Constant = 2;

	protected array $defaultMeta = [
		'#' => [ // default style for path parameters
			self::Pattern => '[^/]+',
			self::FilterOut => [self::class, 'param2path'],
		],
	];

	private string $mask;
	private array $sequence;

	/** regular expression pattern */
	private string $re;

	/** @var string[]  parameter aliases in regular expression */
	private array $aliases = [];

	/** @var array of [value & fixity, filterIn, filterOut] */
	private array $metadata = [];
	private array $xlat = [];

	/** Host, Path, Relative */
	private int $type;

	/** http | https */
	private string $scheme = '';


	/**
	 * @param  string  $mask  e.g. '<presenter>/<action>/<id \d{1,3}>'
	 */
	public function __construct(string $mask, array $metadata = [])
	{
		parent::__construct($mask, $metadata);
		$this->mask = $mask;
		$this->metadata = $this->normalizeMetadata($metadata);
		$this->parseMask($this->detectMaskType());
	}


	/**
	 * Returns default values.
	 */
	public function getDefaults(): array
	{
		$defaults = [];
		foreach ($this->metadata as $name => $meta) {
			if (isset($meta[self::Fixity])) {
				$defaults[$name] = $meta[self::Value];
			}
		}

		return $defaults;
	}


	/** @internal */
	public function getConstantParameters(): array
	{
		$res = [];
		foreach ($this->metadata as $name => $meta) {
			if (isset($meta[self::Fixity]) && $meta[self::Fixity] === self::Constant) {
				$res[$name] = $meta[self::Value];
			}
		}

		return $res;
	}


	/**
	 * Maps HTTP request to an array.
	 */
	public function match(IRequest $httpRequest): ?array
	{
		// combine with precedence: mask (params in URL-path), fixity, query, (post,) defaults

		// 1) URL MASK
		$url = $httpRequest->getUrl();
		$re = $this->re;

		if ($this->type === self::Host) {
			$host = $url->getHost();
			$path = '//' . $host . $url->getPath();
			$parts = ip2long($host)
				? [$host]
				: array_reverse(explode('.', $host));
			$re = strtr($re, [
				'/%basePath%/' => preg_quote($url->getBasePath(), '#'),
				'%tld%' => preg_quote($parts[0], '#'),
				'%domain%' => preg_quote(isset($parts[1]) ? "$parts[1].$parts[0]" : $parts[0], '#'),
				'%sld%' => preg_quote($parts[1] ?? '', '#'),
				'%host%' => preg_quote($host, '#'),
			]);

		} elseif ($this->type === self::Relative) {
			$basePath = $url->getBasePath();
			if (strncmp($url->getPath(), $basePath, strlen($basePath)) !== 0) {
				return null;
			}

			$path = substr($url->getPath(), strlen($basePath));

		} else {
			$path = $url->getPath();
		}

		$path = rawurldecode($path);
		if ($path !== '' && $path[-1] !== '/') {
			$path .= '/';
		}

		if (!$matches = Strings::match($path, $re)) {
			return null; // stop, not matched
		}

		// assigns matched values to parameters
		$params = [];
		foreach ($matches as $k => $v) {
			if (is_string($k) && $v !== '') {
				if (preg_match('/(.*)\[(.*)]/', $this->aliases[$k], $m)) {
					$params[$m[1]][$m[2]] = $v;
				} else {
					$params[$this->aliases[$k]] = $v;
				}
			}
		}

		// 2) CONSTANT FIXITY
		foreach ($this->metadata as $name => $meta) {
			if (isset($meta['metadata'])) {
				foreach($meta['metadata'] as $n => $m) {
					if (!isset($params[$name][$n]) && isset($meta[self::Fixity]) && $meta[self::Fixity] !== self::InQuery) {
						$params[$name][$n] = null; // cannot be overwriten in 3) and detected by isset() in 4)
					}
				}
			} else {
				if (!isset($params[$name]) && isset($meta[self::Fixity]) && $meta[self::Fixity] !== self::InQuery) {
					$params[$name] = null; // cannot be overwriten in 3) and detected by isset() in 4)
				}
			}
		}

		// 3) QUERY
		$params += self::renameKeys($httpRequest->getQuery(), array_flip($this->xlat));

		// 4) APPLY FILTERS & FIXITY
		foreach ($this->metadata as $name => $meta) {
			if (isset($meta['metadata'])) {
				foreach($meta['metadata'] as $n => $m) {
					if (isset($params[$name][$n])) {
						if (!is_scalar($params[$name][$n])) {
							// do nothing
						} elseif (isset($m[self::FilterTable][$params[$name][$n]])) { // applies filterTable only to scalar parameters
							$params[$name][$n] = $meta[self::FilterTable][$params[$name][$n]];

						} elseif (isset($m[self::FilterTable]) && !empty($m[self::FilterStrict])) {
							return null; // rejected by filterTable

						} elseif (isset($m[self::FilterIn])) { // applies filterIn only to scalar parameters
							$params[$name][$n] = $m[self::FilterIn]((string) $params[$name][$n]);
							if ($params[$name][$n] === null && !isset($m[self::Fixity])) {
								return null; // rejected by filter
							}
						}
					} elseif (isset($m[self::Fixity])) {
						$params[$name][$n] = $m[self::Value];
					}
				}
			} else {
				if (isset($params[$name])) {
					if (!is_scalar($params[$name])) {
						// do nothing
					} elseif (isset($meta[self::FilterTable][$params[$name]])) { // applies filterTable only to scalar parameters
						$params[$name] = $meta[self::FilterTable][$params[$name]];

					} elseif (isset($meta[self::FilterTable]) && !empty($meta[self::FilterStrict])) {
						return null; // rejected by filterTable

					} elseif (isset($meta[self::FilterIn])) { // applies filterIn only to scalar parameters
						$params[$name] = $meta[self::FilterIn]((string) $params[$name]);
						if ($params[$name] === null && !isset($meta[self::Fixity])) {
							return null; // rejected by filter
						}
					}
				} elseif (isset($meta[self::Fixity])) {
					$params[$name] = $meta[self::Value];
				}
			}
		}

		if (isset($this->metadata[null][self::FilterIn])) {
			$params = $this->metadata[null][self::FilterIn]($params);
			if ($params === null) {
				return null;
			}
		}

		return $params;
	}


	/**
	 * Constructs absolute URL from array.
	 */
	public function constructUrl(array $params, UrlScript $refUrl): ?string
	{
		if (!$this->preprocessParams($params)) {
			return null;
		}
		$this->renameArrayParams($params);

		$url = $this->compileUrl($params);
		if ($url === null) {
			return null;
		}

		// absolutize
		if ($this->type === self::Relative) {
			$url = (($tmp = $refUrl->getAuthority()) ? "//$tmp" : '') . $refUrl->getBasePath() . $url;

		} elseif ($this->type === self::Path) {
			$url = (($tmp = $refUrl->getAuthority()) ? "//$tmp" : '') . $url;

		} else {
			$host = $refUrl->getHost();
			$parts = ip2long($host)
				? [$host]
				: array_reverse(explode('.', $host));
			$url = strtr($url, [
				'/%basePath%/' => $refUrl->getBasePath(),
				'%tld%' => $parts[0],
				'%domain%' => isset($parts[1]) ? "$parts[1].$parts[0]" : $parts[0],
				'%sld%' => $parts[1] ?? '',
				'%host%' => $host,
			]);
		}

		$url = ($this->scheme ?: $refUrl->getScheme()) . ':' . $url;

		// build query string
		$params = self::renameKeys($params, $this->xlat);
		$sep = ini_get('arg_separator.input');
		$query = http_build_query($params, '', $sep ? $sep[0] : '&');
		if ($query !== '') {
			$url .= '?' . $query;
		}

		return $url;
	}


	private function preprocessParams(array &$params): bool
	{
		$filter = $this->metadata[null][self::FilterOut] ?? null;
		if ($filter) {
			$params = $filter($params);
			if ($params === null) {
				return false; // rejected by global filter
			}
		}

		foreach ($this->metadata as $name => $meta) {
			if (isset($meta['metadata'])) {
				foreach ($meta['metadata'] as $n => $m) {
					$fixity = $m[self::Fixity] ?? null;

					if (!isset($params[$name][$n])) {
						continue; // retains null values
					}

					if (is_scalar($params[$name][$n])) {
						$params[$name][$n] = $params[$name][$n] === false
							? '0'
							: (string) $params[$name][$n];
					}

					if ($fixity !== null) {
						if ($params[$name][$n] === $m[self::Value]) { // remove default values; null values are retain
							unset($params[$name][$n]);
							continue;

						} elseif ($fixity === self::Constant) {
							return false; // wrong parameter value
						}
					}

					if (is_scalar($params[$name][$n]) && isset($m[self::FilterTableOut][$params[$name][$n]])) {
						$params[$name][$n] = $m[self::FilterTableOut][$params[$name][$n]];

					} elseif (isset($m[self::FilterTableOut]) && !empty($m[self::FilterStrict])) {
						return false;

					} elseif (isset($m[self::FilterOut])) {
						$params[$name][$n] = $m[self::FilterOut]($params[$name][$n]);
					}

					if (
						isset($m[self::Pattern])
						&& !preg_match("#(?:{$m[self::Pattern]})$#DA", rawurldecode((string) $params[$name][$n]))
					) {
						return false; // pattern not match
					}
				}
			} else {
				$fixity = $meta[self::Fixity] ?? null;

				if (!isset($params[$name])) {
					continue; // retains null values
				}

				if (is_scalar($params[$name])) {
					$params[$name] = $params[$name] === false
						? '0'
						: (string) $params[$name];
				}

				if ($fixity !== null) {
					if ($params[$name] === $meta[self::Value]) { // remove default values; null values are retain
						unset($params[$name]);
						continue;

					} elseif ($fixity === self::Constant) {
						return false; // wrong parameter value
					}
				}

				if (is_scalar($params[$name]) && isset($meta[self::FilterTableOut][$params[$name]])) {
					$params[$name] = $meta[self::FilterTableOut][$params[$name]];

				} elseif (isset($meta[self::FilterTableOut]) && !empty($meta[self::FilterStrict])) {
					return false;

				} elseif (isset($meta[self::FilterOut])) {
					$params[$name] = $meta[self::FilterOut]($params[$name]);
				}

				if (
					isset($meta[self::Pattern])
					&& !preg_match("#(?:{$meta[self::Pattern]})$#DA", rawurldecode((string) $params[$name]))
				) {
					return false; // pattern not match
				}
			}
		}

		return true;
	}


	private function renameArrayParams(array &$params): void
	{
		foreach ($params as $name => $value) {
			if (!is_array($value)) {
				continue;
			}
			foreach ($value as $subName => $subValue) {
				$params[$name . '[' . $subName . ']'] = $subValue;
			}
			unset($params[$name]);
		}
	}


	private function compileUrl(array &$params): ?string
	{
		$brackets = [];
		$required = null; // null for auto-optional
		$path = '';
		$i = count($this->sequence) - 1;

		do {
			$path = $this->sequence[$i] . $path;
			if ($i === 0) {
				return $path;
			}

			$i--;

			$name = $this->sequence[$i--]; // parameter name

			if ($name === ']') { // opening optional part
				$brackets[] = $path;

			} elseif ($name[0] === '[') { // closing optional part
				$tmp = array_pop($brackets);
				if ($required < count($brackets) + 1) { // is this level optional?
					if ($name !== '[!') { // and not "required"-optional
						$path = $tmp;
					}
				} else {
					$required = count($brackets);
				}
			} elseif ($name[0] === '?') { // "foo" parameter
				continue;

			} elseif (isset($params[$name]) && $params[$name] !== '') {
				$required = count($brackets); // make this level required
				$path = $params[$name] . $path;
				unset($params[$name]);

			} elseif (isset($this->metadata[$name][self::Fixity])) { // has default value?
				$path = $required === null && !$brackets // auto-optional
					? ''
					: $this->metadata[$name][self::Default] . $path;

			} else {
				return null; // missing parameter '$name'
			}
		} while (true);
	}


	private function detectMaskType(): string
	{
		// '//host/path' vs. '/abs. path' vs. 'relative path'
		if (preg_match('#(?:(https?):)?(//.*)#A', $this->mask, $m)) {
			$this->type = self::Host;
			[, $this->scheme, $path] = $m;
			return $path;

		} elseif (str_starts_with($this->mask, '/')) {
			$this->type = self::Path;

		} else {
			$this->type = self::Relative;
		}

		return $this->mask;
	}


	private function normalizeMetadata(array $metadata): array
	{
		foreach ($metadata as $name => $meta) {
			if (isset($meta['metadata'])) {
				foreach ($meta['metadata'] as $n => $m) {
					$metadata[$n] = $this->normalizeMetadata($m);
				}
			} else {
				if (!is_array($meta)) {
					$metadata[$name] = $meta = [self::Value => $meta];
				}
				if (array_key_exists(self::Value, $meta)) {
					if (is_scalar($meta[self::Value])) {
						$metadata[$name][self::Value] = $meta[self::Value] === false
							? '0'
							: (string) $meta[self::Value];
					}

					$metadata[$name]['fixity'] = self::Constant;
				}
			}
		}

		return $metadata;
	}


	private function parseMask(string $path): void
	{
		// <parameter-name[=default] [pattern]> or [ or ] or ?...
		$parts = Strings::split($path, '/<([^<>= ]+)(=[^<> ]*)? *([^<>]*)>|(\[!?|\]|\s*\?.*)/');

		$i = count($parts) - 1;
		if ($i === 0) {
			$this->re = '#' . preg_quote($parts[0], '#') . '/?$#DA';
			$this->sequence = [$parts[0]];
			return;
		}

		if ($this->parseQuery($parts)) {
			$i -= 5;
		}

		$brackets = 0; // optional level
		$re = '';
		$sequence = [];
		$autoOptional = true;

		do {
			$part = $parts[$i]; // part of path
			if (strpbrk($part, '<>') !== false) {
				throw new InvalidArgumentException("Unexpected '$part' in mask '$this->mask'.");
			}

			array_unshift($sequence, $part);
			$re = preg_quote($part, '#') . $re;
			if ($i === 0) {
				break;
			}

			$i--;

			$part = $parts[$i]; // [ or ]
			if ($part === '[' || $part === ']' || $part === '[!') {
				$brackets += $part[0] === '[' ? -1 : 1;
				if ($brackets < 0) {
					throw new InvalidArgumentException("Unexpected '$part' in mask '$this->mask'.");
				}

				array_unshift($sequence, $part);
				$re = ($part[0] === '[' ? '(?:' : ')?') . $re;
				$i -= 4;
				continue;
			}

			$pattern = trim($parts[$i--]); // validation condition (as regexp)
			$default = $parts[$i--]; // default value
			$name = $parts[$i--]; // parameter name
			array_unshift($sequence, $name);

			if ($name[0] === '?') { // "foo" parameter
				$name = substr($name, 1);
				$re = $pattern
					? '(?:' . preg_quote($name, '#') . "|$pattern)$re"
					: preg_quote($name, '#') . $re;
				$sequence[1] = $name . $sequence[1];
				continue;
			}

			// pattern, condition & metadata
			$meta = ($this->metadata[$name] ?? []) + ($this->defaultMeta[$name] ?? $this->defaultMeta['#']);

			if ($pattern === '' && isset($meta[self::Pattern])) {
				$pattern = $meta[self::Pattern];
			}

			if ($default !== '') {
				$meta[self::Value] = substr($default, 1);
				$meta[self::Fixity] = self::InPath;
			}

			$meta[self::FilterTableOut] = empty($meta[self::FilterTable])
				? null
				: array_flip($meta[self::FilterTable]);
			if (array_key_exists(self::Value, $meta)) {
				if (isset($meta[self::FilterTableOut][$meta[self::Value]])) {
					$meta[self::Default] = $meta[self::FilterTableOut][$meta[self::Value]];

				} elseif (isset($meta[self::Value], $meta[self::FilterOut])) {
					$meta[self::Default] = $meta[self::FilterOut]($meta[self::Value]);

				} else {
					$meta[self::Default] = $meta[self::Value];
				}
			}

			$meta[self::Pattern] = $pattern;

			// include in expression
			$this->aliases['p' . $i] = $name;
			$re = '(?P<p' . $i . '>(?U)' . $pattern . ')' . $re;
			if ($brackets) { // is in brackets?
				if (!isset($meta[self::Value])) {
					$meta[self::Value] = $meta[self::Default] = null;
				}

				$meta[self::Fixity] = self::InPath;

			} elseif (isset($meta[self::Fixity])) {
				if ($autoOptional) {
					$re = '(?:' . $re . ')?';
				}

				$meta[self::Fixity] = self::InPath;

			} else {
				$autoOptional = false;
			}

			if (preg_match('/(.*)\[(.*)\]/', $name, $m)) {
				$this->metadata[$m[1]]['metadata'][$m[2]] = $meta;
			} else {
				$this->metadata[$name] = $meta;
			}
		} while (true);

		if ($brackets) {
			throw new InvalidArgumentException("Missing '[' in mask '$this->mask'.");
		}

		$this->re = '#' . $re . '/?$#DA';
		$this->sequence = $sequence;
	}


	private function parseQuery(array $parts): bool
	{
		$query = $parts[count($parts) - 2] ?? '';
		if (!str_starts_with(ltrim($query), '?')) {
			return false;
		}

		// name=<parameter-name [pattern]>
		$matches = Strings::matchAll($query, '/(?:([a-zA-Z0-9_.-]+)=)?<([^> ]+) *([^>]*)>/');

		foreach ($matches as [, $param, $name, $pattern]) { // $pattern is not used
			$meta = ($this->metadata[$name] ?? []) + ($this->defaultMeta['?' . $name] ?? []);

			if (array_key_exists(self::Value, $meta)) {
				$meta[self::Fixity] = self::InQuery;
			}

			unset($meta[self::Pattern]);
			$meta[self::FilterTableOut] = empty($meta[self::FilterTable])
				? null
				: array_flip($meta[self::FilterTable]);

			$this->metadata[$name] = $meta;
			if ($param !== '') {
				$this->xlat[$name] = $param;
			}
		}

		return true;
	}


	/********************* Utilities ****************d*g**/


	/**
	 * Rename keys in array.
	 */
	private static function renameKeys(array $arr, array $xlat): array
	{
		if (!$xlat) {
			return $arr;
		}

		$res = [];
		$occupied = array_flip($xlat);
		foreach ($arr as $k => $v) {
			if (isset($xlat[$k])) {
				$res[$xlat[$k]] = $v;

			} elseif (!isset($occupied[$k])) {
				$res[$k] = $v;
			}
		}

		return $res;
	}
}