<?php

declare(strict_types=1);

namespace Webovac\Core\Presenter\Authorization;

use App\Lib\ResourceGenerator\ToArrayConverterWithoutMany;
use App\Model\DataModel;
use App\Model\Web\WebData;
use Nette\Application\Attributes\Persistent;
use Nette\DI\Attributes\Inject;
use Stepapo\OAuth2\Application\OAuthPresenter;
use Stepapo\OAuth2\Grant\IGrant;
use Stepapo\OAuth2\OAuthException;
use Webovac\Core\Lib\DataProvider;


class AuthorizationPresenter extends OAuthPresenter
{
	#[Persistent] public string $host;
	#[Persistent] public string $basePath;
	#[Persistent] public string $lang;
	#[Inject] public DataProvider $dataProvider;
	#[Inject] public DataModel $dataModel;
	private ?WebData $webData;


	public function startup()
	{
		$this->webData = $this->dataModel->getWebDataByHost($this->host, $this->basePath);
		$languageData = $this->dataModel->getLanguageDataByShortcut($this->lang);
		$this->dataProvider
			->setLanguageData($languageData)
			->setWebData($this->webData);
		parent::startup();
	}

	public function actionAuthorize(string $response_type, string $redirect_uri, ?string $scope)
	{
//		if (!$this->user->isLoggedIn()) {
//			$this->redirect('AnyUser:login', ['backlink' => $this->storeRequest()]);
//		}
		if ($response_type == 'code') {
			$this->issueAuthorizationCode($response_type, $redirect_uri, $scope);
		} else if ($response_type == 'token') {
			$this->issueAccessToken(IGrant::IMPLICIT, $redirect_uri);
		}
	}


	public function actionToken(?string $grant_type = null)
	{
		try {
			$this->issueAccessToken($grant_type);
		} catch (OAuthException $e) {
			$this->oauthError($e);
		}
	}
}
