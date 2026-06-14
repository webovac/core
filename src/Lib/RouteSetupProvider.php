<?php

declare(strict_types=1);

namespace Webovac\Core\Lib;

use Build\Model\DataModel;
use Build\Model\Page\Page;
use Nette\Caching\Cache;
use Nette\Caching\Storage;
use Nette\Http\IRequest;
use Stepapo\Utils\Service;


class RouteSetupProvider implements Service
{
	private Cache $cache;
	private array $setup;


	public function __construct(
		private DataModel $dataModel,
		private Storage $storage,
		private IRequest $request,
	) {
		$this->cache = new Cache($this->storage, 'cms');
	}


	public function getSetup(): array
	{
		if (!isset($this->setup)) {
			$this->setup = $this->cache->load('routeSetup', function() {
				ini_set('memory_limit', '1G');
				$setup = [
					'mapIn' => [],
					'mapOut' => [],
					'parts' => [],
				];
				foreach ($this->dataModel->languageDataRepository->findAllPairs() as $shortcut) {
					$setup['parts'][$shortcut] = $shortcut;
				}
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
						if (isset($translationData->paths)) {
							foreach ($translationData->paths as $path) {
								if ($path->web !== $pageData->web) {
									continue;
								}
								$f = $path->path;
								preg_match_all('/<id\[(.+?)\]>/', $f, $m);
								$f = preg_replace('/<id\[(.+?)\]>/', '<id>', $f);
								$f = trim($f, '/');
								foreach (explode('/', $f) as $part) {
									if ($part === '<id>' || isset($setup['parts'][$part])) {
										continue;
									}
									$setup['parts'][$part] = $part;
								}
								$l = $p->defaultLanguage === $languageData->id ? null : $languageData->shortcut;
								$fullPath = implode('/', array_filter([$l, $f]));
								$setup['mapIn'][$base][$fullPath] = [
									'host' => $p->host,
									'basePath' => $p->basePath,
									'pageName' => $p->name,
									'lang' => $languageData->shortcut,
									'id' => $m[1],
									'path' => $f === '<path .+>' ? $f : null,
									'signals' => $signals,
									'parameters' => $parameters,
								];
								if ($path->active) {
									$setup['mapOut'][$base][$languageData->shortcut][$p->name] = [
										'host' => $p->host,
										'basePath' => $p->basePath,
										'p' => $fullPath,
										'signals' => array_flip($signals),
										'parameters' => array_flip($parameters),
									];
								}
							}
						}
					}
				}
				return $setup;
			}, [Cache::Tags => ['language', 'web', 'page', 'router']]);
		}
		return $this->setup;
	}
}