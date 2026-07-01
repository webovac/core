<?php

declare(strict_types=1);

namespace Webovac\Core\Lib;

use Build\Model\DataModel;
use Build\Model\Page\Page;
use Build\Model\Web\WebData;
use Nette\Caching\Cache;
use Nette\Caching\Storage;
use Stepapo\Utils\Service;


class RouteSetupProvider implements Service
{
	private Cache $cache;
	private array $webSetup;
	private array $pageSetup;


	public function __construct(
		private DataModel $dataModel,
		private Storage $storage,
	) {
		$this->cache = new Cache($this->storage, 'cms');
	}


	public function getWebSetup(): array
	{
		if (!isset($this->webSetup)) {
			$this->webSetup = $this->cache->load('webSetup', function() {
				$webDatas = $this->dataModel->findWebDatas();
				$apiModuleData = $this->dataModel->getModuleDataByName('Api');
				$webSetup = [];
				foreach ($webDatas as $webData) {
					$webSetup[] = [
						'mask' => $webData->getStyleRouteMask(),
						'metadata' => $webData->getRouteMetadata('Core:Style'),
					];
					foreach ($webData->translations as $webTranslationData) {
						$languageData = $this->dataModel->getLanguageData($webTranslationData->language);
						$webSetup[] = [
							'mask' => $webData->getManifestRouteMask($webTranslationData->language === $webData->defaultLanguage ? null : $languageData->shortcut),
							'metadata' => $webData->getRouteMetadata('Core:Manifest', $languageData->shortcut),
						];
						if ($apiModuleData && in_array($apiModuleData->id, $webData->modules, true)) {
							$webSetup[] = [
								'mask' => $webData->getAuthorizationRouteMask($webTranslationData->language === $webData->defaultLanguage ? null : $languageData->shortcut),
								'metadata' => $webData->getRouteMetadata('Api:Authorization', $languageData->shortcut, 'authorize'),
							];
							$webSetup[] = [
								'mask' => $webData->getApiRouteMask($webTranslationData->language === $webData->defaultLanguage ? null : $languageData->shortcut),
								'metadata' => $webData->getRouteMetadata('Api:Home', $languageData->shortcut),
								'type' => 'crud',
							];
						}
					}
				}
				/** @var WebData $webData */
				foreach (array_reverse((array) $webDatas) as $webData) {
					$webSetup[] = [
						'mask' => $webData->getPageRouteMask(),
						'metadata' => [
							'presenter' => 'Core:Home',
							'action' => 'default',
							'host' => $webData->host,
							'basePath' => $webData->basePath,
						],
						'type' => 'page',
					];
				}
				return $webSetup;
			}, [Cache::Tags => ['language', 'web', 'page', 'router']]);
		}
		return $this->webSetup;
	}


	public function getPageSetup(): array
	{
		if (!isset($this->pageSetup)) {
			$this->pageSetup = $this->cache->load('pageSetup', function() {
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
		return $this->pageSetup;
	}
}
