<?php

declare(strict_types=1);

namespace Webovac\Core\Router;

use App\Model\DataModel;
use App\Model\Page\Page;


final class RouterFactory
{
	public function __construct(
		private DataModel $dataModel,
	) {}


	public function create(): CmsRouteList
	{
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
			$queryNames = [];
			if (isset($pageData->queryNames)) {
				foreach ($pageData->queryNames as $queryName) {
					$queryNames[] = "$queryName->query=<$queryName->parameter>";
				}
			}
			foreach ($pageData->translations as $translationData) {
				$languageData = $this->dataModel->languageRepository->getById($translationData->language);
				if ($pageData->redirectPage) {
					$p = $this->dataModel->pageRepository->getById($pageData->web . '-' . $pageData->redirectPage);
				} else {
					$p = $pageData;
				}
				$routeList->addRoute(
					mask: $translationData->fullPath . ($queryNames ? (' ? ' . implode(' & ', $queryNames)) : ''),
					metadata: [
						'presenter' => 'Home',
						'action' => 'default',
						'host' => $p->host,
						'basePath' => $p->basePath,
						'pageName' => $p->name,
						'lang' => $languageData->shortcut,
					],
					oneWay: (bool) $pageData->redirectPage,
				);
			}
		}
		return $routeList;
	}
}
