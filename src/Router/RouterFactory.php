<?php

declare(strict_types=1);

namespace Webovac\Core\Router;

use App\Model\DataModel;
use App\Model\Page\Page;
use Nette\Caching\Cache;
use Nette\Routing\Route;


final class RouterFactory
{
	public function __construct(
		private DataModel $dataModel,
		private Cache $cache,
	) {}


	public function create(): CmsRouteList
	{
		return $this->cache->load('routeList', function() {
			$routeList = new CmsRouteList;
			foreach ($this->dataModel->webRepository->findAll() as $webData) {
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
			foreach ($this->dataModel->pageRepository->findAll() as $pageData) {
				if ($pageData->type !== Page::TYPE_PAGE) {
					continue;
				}
				$parameters = [];
				if (isset($pageData->parameters)) {
					foreach ($pageData->parameters as $parameter) {
						$parameters[] = "$parameter->query=<$parameter->parameter>";
					}
				}
				$signals = [];
				if (isset($pageData->signals)) {
					foreach ($pageData->signals as $signal) {
						$signals[$signal->name] = $signal->signal;
					}
				}
				foreach ($pageData->translations as $translationData) {
					$languageData = $this->dataModel->languageRepository->getById($translationData->language);
					if ($pageData->redirectPage) {
						$p = $this->dataModel->pageRepository->getById($pageData->web . '-' . $pageData->redirectPage);
					} else {
						$p = $pageData;
					}
					$metadata = [
						'presenter' => 'Home',
						'action' => 'default',
						'host' => $p->host,
						'basePath' => $p->basePath,
						'pageName' => $p->name,
						'lang' => $languageData->shortcut,
					];
					if ($signals) {
						$metadata['do'] = [Route::FilterTable => $signals];
					}
					$routeList->addRoute(
						mask: $translationData->fullPath . ($parameters || $signals ? (' ? ' . implode(' & ', $parameters) . ($signals ? ' & do=<do>' : ''))  : ''),
						metadata: $metadata,
						oneWay: (bool) $pageData->redirectPage,
					);
				}
			}
			return $routeList;
		});
	}
}
