<?php

declare(strict_types=1);

namespace Webovac\Core\Router;

use App\Model\DataModel;
use App\Model\Page\Page;
use Nette\Application\BadRequestException;
use Nette\Caching\Cache;
use Nette\Http\IRequest;
use Nette\Routing\Route;
use Nette\Routing\RouteList;


final class RouterFactory
{
	private array $setup;


	public function __construct(
		private DataModel $dataModel,
		private Cache $cache,
		private IRequest $request,
	) {}


	public function create(): RouteList
	{
		$routeList = new RouteList;
		$webDatas = $this->dataModel->findWebDatas();
		foreach ($webDatas as $webData) {
			$routeList->addRoute(
				mask: $webData->getStyleRouteMask(),
				metadata: $webData->getStyleRouteMetadata(),
			);
			foreach ($webData->translations as $webTranslationData) {
				$languageData = $this->dataModel->getLanguageData($webTranslationData->language);
				$routeList->addRoute(
					mask: $webData->getManifestRouteMask($webTranslationData->language === $webData->defaultLanguage ? null : $languageData->shortcut),
					metadata: $webData->getManifestRouteMetadata($languageData->shortcut),
				);
			}
		}
		foreach (array_reverse((array) $webDatas) as $webData) {
			$routeList->addRoute(
				mask: $webData->getPageRouteMask(),
				metadata: [
					'presenter' => 'Home',
					'action' => 'default',
					'host' => $webData->host,
					'basePath' => $webData->basePath,
					null => [
						Route::FilterIn => $this->filterIn(...),
						Route::FilterOut => $this->filterOut(...),
					],
				],
			);
		}
		return $routeList;
	}


	private function filterIn(array $params): array
	{
		$setup = $this->getSetup();
		$p = explode('/', $params['p'] ?? '');
		$ids = [];
		foreach ($p as $key => $part) {
			if (isset($setup['parts'][$part])) {
				continue;
			}
			$p[$key] = '<id>';
			$ids[] = $part;
		}
		$p = implode('/', $p);
		$do = $params['do'] ?? null;
		$base = '//' . $params['host'] . ($params['basePath'] ? '/' . $params['basePath'] : '');
		$pageIn = $setup['mapIn'][$base][$p] ?? null;
		if (!$pageIn) {
			foreach ($setup['mapIn'][$base] as $pagePath => $pageSetup) {
				if (!str_contains($pagePath, '<id>')) {
					continue;
				}
				$pParts = explode('/', $p);
				$pagePathParts = explode('/', $pagePath);
				if (count($pParts) !== count($pagePathParts)) {
					continue;
				}
				$ids = [];
				foreach ($pagePathParts as $key => $part) {
					if ($part === '<id>') {
						$ids[] = $pParts[$key];
						$pParts[$key] = '<id>';
					}
				}
				$newP = implode('/', $pParts);
				if ($newP === $pagePath) {
					$pageIn = $pageSetup;
					break;
				}
			}
		}
		if (!$pageIn) {
			$pageIn = $setup['mapIn'][$base]['<path .+>'] ?? null;
		}
		if (!$pageIn) {
			throw new BadRequestException;
		}
		$return = [
			'presenter' => 'Home',
			'action' => 'default',
			'host' => $pageIn['host'],
			'basePath' => $pageIn['basePath'],
			'pageName' => $pageIn['pageName'],
			'lang' => $pageIn['lang'],
		];
		if ($pageIn['id']) {
			$newIds = [];
			foreach ($pageIn['id'] as $key => $name) {
				$newIds[$name] = $ids[$key];
			}
			$return['id'] = $newIds;
		}
		if ($pageIn['path']) {
			$return['path'] = $params['p'];
		}
		if ($do) {
			$return['do'] = $pageIn['signals'][$do] ?? $do;
		}
		foreach ($params as $key => $value) {
			$name = $pageIn['parameters'][$key] ?? $key;
			if (isset($return[$name])) {
				continue;
			}
			$return[$name] = $value;
		}
		return $return;
	}


	private function filterOut(array $params): array
	{
		$setup = $this->getSetup();
		$pageName = $params['pageName'];
		$lang = $params['lang'] ?? 'cs';
		$do = $params['do'] ?? null;
		$id = array_values($params['id'] ?? []);
		$host = $params['host'] ?? $this->request->getUrl()->getHost();
		$basePath = $params['basePath'] ?? null;
		//$path = $params['path'];
		$base = '//' . $host . ($basePath ? '/' . $basePath : '');
		$pageOut = $setup['mapOut'][$base][$lang][$pageName];
		if ($p = $pageOut['p']) {
			$p = explode('/', $pageOut['p']);
			foreach ($p as $key => $word) {
				if ($word === '<id>') {
					$p[$key] = array_shift($id);
				}
				if ($word === '<path .+>') {
					$p[$key] = $params['path'];
				}
			}
			$p = implode('/', $p);
		}
		$return = [
			'presenter' => 'Home',
			'action' => 'default',
			'host' => $pageOut['host'],
			'basePath' => $pageOut['basePath'],
			'p' => $p ?: null,
		];
		if ($do) {
			$return['do'] = $pageOut['signals'][$do] ?? $do;
		}
		foreach ($params as $key => $value) {
			$name = $pageOut['parameters'][$key] ?? $key;
			if (isset($return[$name]) || in_array($key, ['pageName', 'lang', 'id', 'path'], true)) {
				continue;
			}
			$return[$name] = $value;
		}
		return $return;
	}


	private function getSetup(): array
	{
		if (!isset($this->setup)) {
			$this->setup = $this->cache->load('routeSetup', function() {
				$setup = [
					'mapIn' => [],
					'mapOut' => [],
					'parts' => [],
				];
				foreach ($this->dataModel->findPageDatas() as $pageData) {
					if ($pageData->type !== Page::TYPE_PAGE) {
						continue;
					}
					foreach ($pageData->translations as $translationData) {
						$languageData = $this->dataModel->getLanguageData($translationData->language);
						if ($pageData->redirectPage) {
							$p = $this->dataModel->getPageData($pageData->web, $pageData->redirectPage);
						} else {
							$p = $pageData;
						}
						$base = '//' . $p->host . ($p->basePath ? '/' . $p->basePath : '');
						$fullPath = str_replace($base, '', $translationData->fullPath);
						preg_match_all('/<id\[(.+?)\]>/', $fullPath, $m);
						$fullPath = preg_replace('/<id\[(.+?)\]>/', '<id>', $fullPath);
						$fullPath = trim($fullPath, '/');
						foreach (explode('/', $fullPath) as $part) {
							if ($part === '<id>' || isset($setup['parts'][$part])) {
								continue;
							}
							$setup['parts'][$part] = $part;
						}
						$parameters = [];
						if (isset($pageData->parameters)) {
							foreach ($pageData->parameters as $parameter) {
								$parameters[$parameter->query] = $parameter->parameter;
							}
						}
						$signals = [];
						if (isset($pageData->signals)) {
							foreach ($pageData->signals as $signal) {
								$signals[$signal->name] = $signal->signal;
							}
						}
						$setup['mapIn'][$base][$fullPath] = [
							'presenter' => 'Home',
							'action' => 'default',
							'host' => $p->host,
							'basePath' => $p->basePath,
							'pageName' => $p->name,
							'lang' => $languageData->shortcut,
							'id' => $m[1],
							'path' => $fullPath === '<path .+>' ? $fullPath : null,
							'signals' => $signals,
							'parameters' => $parameters,
						];
						$setup['mapOut'][$base][$languageData->shortcut][$p->name] = [
							'presenter' => 'Home',
							'action' => 'default',
							'host' => $p->host,
							'basePath' => $p->basePath,
							'p' => $fullPath,
							'signals' => array_flip($signals),
							'parameters' => array_flip($parameters),
						];
					}
				}
				return $setup;
			}, [Cache::Tags => ['language', 'web', 'page']]);
		}
		return $this->setup;
	}
}
