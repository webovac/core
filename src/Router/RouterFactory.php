<?php

declare(strict_types=1);

namespace Webovac\Core\Router;

use App\Model\DataModel;
use App\Model\Page\Page;
use Nette\Application\Routers\RouteList;


final class RouterFactory
{
	public function __construct(
		private DataModel $dataModel,
	) {}


	public function create(): RouteList
	{
		$routeList = new RouteList;
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
			foreach ($pageData->translations as $translationData) {
				$languageData = $this->dataModel->languageRepository->getById($translationData->language);
				if ($pageData->hasParentParameter) {
					$translationData->fullPath = preg_replace('/(<id>)(\/.*\/<id>)/', '<parentId>$2', $translationData->fullPath);
				}
				if ($pageData->redirectPage) {
					$p = $this->dataModel->pageRepository->getBy($pageData->web . '-' . $pageData->redirectPage);
				} else {
					$p = $pageData;
				}
				$routeList->addRoute(
					mask: $translationData->fullPath,
					metadata: [
						'presenter' => 'Home',
						'action' => 'default',
						'host' => $p->host,
						'basePath' => $p->basePath,
						'pageName' => $p->name,
						'lang' => $languageData->shortcut,
					],
					oneWay: $pageData->redirectPage ? true : false,
				);
			}
		}
		return $routeList;
	}
}
