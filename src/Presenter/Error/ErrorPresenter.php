<?php

declare(strict_types=1);

namespace Webovac\Core\Presenter\Error;

use Nette\Application\BadRequestException;
use Nette\Application\Helpers;
use Nette\Application\IPresenter;
use Nette\Application\Request;
use Nette\Application\Response;
use Nette\Application\Responses\CallbackResponse;
use Nette\Application\Responses\ForwardResponse;
use Nette\Http\IRequest;
use Nette\Http\IResponse;
use Tracy\ILogger;


final class ErrorPresenter implements IPresenter
{
	public function __construct(
		private ILogger $logger,
	) {}


	public function run(Request $request): Response
	{
		$e = $request->getParameter('exception');

		if ($e instanceof BadRequestException) {
			// $this->logger->log("HTTP code {$e->getCode()}: {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}", 'access');
			[$module, , $sep] = Helpers::splitName($request->getPresenterName());
			$errorPresenter = $module . $sep . 'Error4xx';
			return new ForwardResponse($request->setPresenterName($errorPresenter));
		}

		$this->logger->log($e, ILogger::EXCEPTION);
		return new CallbackResponse(function (IRequest $httpRequest, IResponse $httpResponse): void {
			if (preg_match('#^text/html(?:;|$)#', $httpResponse->getHeader('Content-Type'))) {
				require __DIR__ . '/500.phtml';
			}
		});
	}
}
