<?php

declare(strict_types=1);

namespace Webovac\Core\Router;

use Build\Model\DataModel;
use Build\Model\Web\WebData;
use Nette\Application\BadRequestException;
use Nette\Application\Routers\RouteList;
use Nette\Http\IRequest;
use Nette\Routing\Route;
use Stepapo\Restful\Application\Routes\CrudRoute;
use Stepapo\Utils\Service;
use Webovac\Core\Lib\RouteSetupProvider;


final class CmsRouterFactory implements Service
{
	private array $setup;
	private array $webSetup;


	public function __construct(
		private DataModel $dataModel,
		private IRequest $request,
		private RouteSetupProvider $routeSetupProvider,
	) {}


	public function create(): RouteList
	{
		$routeList = new RouteList;
		foreach ($this->routeSetupProvider->getWebSetup() as $route) {
			if (isset($route['type']) && $route['type'] === 'crud') {
				$routeList->add(new CrudRoute(
					mask: $route['mask'],
					metadata: $route['metadata'],
				));
			} else if (isset($route['type']) && $route['type'] === 'page') {
				$routeList->addRoute(
					mask: $route['mask'],
					metadata: $route['metadata'] + ['' => [
						Route::FilterIn => $this->filterIn(...),
						Route::FilterOut => $this->filterOut(...),
					]],
				);
			} else {
				$routeList->addRoute(
					mask: $route['mask'],
					metadata: $route['metadata'],
				);
			}
		}
		return $routeList;
	}


	private function filterIn(array $params): array
	{
		$setup = $this->routeSetupProvider->getPageSetup();
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
				if (!str_contains((string) $pagePath, '<id>')) {
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
			'presenter' => 'Core:Home',
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
		$setup = $this->routeSetupProvider->getPageSetup();
		$pageName = $params['pageName'];
		$lang = $params['lang'] ?? 'cs';
		$do = $params['do'] ?? null;
		$id = array_values($params['id'] ?? []);
		$host = $params['host'] ?? $this->request->getUrl()->getHost();
		$basePath = $params['basePath'] ?? null;
		$base = "//$host/" . ($basePath ?: '~');
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
			'presenter' => 'Core:Home',
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
}
