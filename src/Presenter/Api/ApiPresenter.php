<?php

declare(strict_types=1);

namespace Webovac\Core\Presenter\Api;

use App\Lib\ResourceGenerator\ResourceGenerator;
use App\Lib\ResourceGenerator\ToArrayConverterWithoutMany;
use App\Model\DataModel;
use App\Model\Orm;
use App\Model\Web\WebData;
use Nette\Application\Attributes\Parameter;
use Nette\Application\Attributes\Persistent;
use Nette\DI\Attributes\Inject;
use Nette\InvalidArgumentException;
use Nette\Schema\ValidationException;
use Nextras\Orm\Collection\ICollection;
use Nextras\Orm\Entity\IEntity;
use Nextras\Orm\Relationships\IRelationshipCollection;
use Stepapo\Restful\Application\BadRequestException;
use Stepapo\Restful\Application\UI\ResourcePresenter;
use Stepapo\Restful\Security\Process\OAuth2Authentication;
use Webovac\Core\Lib\DataProvider;
use Webovac\Core\Lib\PropertyChecker;
use Webovac\Core\Model\CmsRepository;


class ApiPresenter extends ResourcePresenter
{
	#[Persistent] public string $host;
	#[Persistent] public string $basePath;
	#[Persistent] public string $lang;
	#[Parameter] public string $entityName;
	#[Parameter] public mixed $id = null;
	#[Parameter] public ?string $related = null;
	#[Inject] public Orm $orm;
	#[Inject] public OAuth2Authentication $authenticationProcess;
	#[Inject] public ResourceGenerator $resourceGenerator;
	#[Inject] public DataProvider $dataProvider;
	#[Inject] public DataModel $dataModel;
	#[Inject] public PropertyChecker $propertyChecker;
	private ?IEntity $item = null;
	private ?WebData $webData;


	public function startup()
	{
		parent::startup();
//		$this->authentication->setAuthProcess($this->authenticationProcess);
		$this->webData = $this->dataModel->getWebDataByHost($this->host, $this->basePath);
		$languageData = $this->dataModel->getLanguageDataByShortcut($this->lang);
		$this->dataProvider
			->setLanguageData($languageData)
			->setWebData($this->webData);
		if (!$this->entityName) {
			$this->sendErrorResource(BadRequestException::notFound('No entity.'));
		}
		if ($this->id) {
			$this->item = $this->getCollection()->getById($this->id);
			if (!$this->item) {
				$this->sendErrorResource(BadRequestException::notFound("No entity '$this->entityName' with ID '$this->id' found."));
			}
		}
	}


	private function getCollection(): ICollection
	{
		try {
			/** @var CmsRepository $repository */
			$repository = $this->orm->getRepositoryByName($this->entityName . 'Repository');
			if ($repository->isForbiddenRepository($this->webData)) {
				throw new \InvalidArgumentException;
			}
		} catch (\Exception $e) {
			$this->sendErrorResource(BadRequestException::notFound("Entity '$this->entityName' not found."));
		}
		if ($repository->shouldFilterByWeb($this->webData)) {
			return $repository->findBy($repository->getFilterByWeb($this->webData));
		}
		return $repository->findAll();
	}


	public function actionRead()
	{
		if ($this->item) {
			if ($this->related) {
				try {
					if (!$this->item->{$this->related} instanceOf IRelationshipCollection){
						throw new InvalidArgumentException("'$this->related' is not a collection.");
					}
					/** @var CmsRepository $repository */
					$repository = $this->orm->getRepository($this->item->getMetadata()->getProperty($this->related)->relationship->repository);
					if ($repository->isForbiddenRepository($this->webData)) {
						throw new InvalidArgumentException("'$this->related' is not a collection.");
					}
					$collection = $this->item->getProperty($this->related)->toCollection();
					if ($repository->shouldFilterByWeb($this->webData)) {
						$collection->findBy($repository->getFilterByWeb($this->webData));
					}
					$this->sendCollectionResource($this->item->getProperty($this->related)->toCollection());
				} catch (InvalidArgumentException $e) {
					$this->sendErrorResource(BadRequestException::notFound($e->getMessage()));
				}
			} else {
				$this->sendEntityResource($this->item);
			}
		} else {
			$this->sendCollectionResource($this->getCollection());
		}
	}


	private function sendEntityResource(IEntity $entity)
	{
		$this->resource = $this->resourceGenerator->createFromArrayQuery(
			$entity,
			$this->getQueryParameters(),
			checkProperty: $this->propertyChecker->isForbiddenProperty(...),
		);
	}


	private function sendCollectionResource(ICollection $collection)
	{
		try {
			$this->resource = $this->resourceGenerator->createFromArrayQuery(
				$collection,
				$this->getQueryParameters(),
				checkProperty: $this->propertyChecker->isForbiddenProperty(...),
			);
		} catch (BadRequestException $e) {
			$this->sendErrorResource(BadRequestException::notFound($e->getMessage()));
		} catch (ValidationException $e) {
			$this->sendErrorResource(BadRequestException::notFound($e->getMessage()));
		}
	}


	private function getQueryParameters(): array
	{
		$parameters = $this->getParameters();
		unset(
			$parameters['host'],
			$parameters['basePath'],
			$parameters['lang'],
			$parameters['entityName'],
			$parameters['id'],
			$parameters['related'],
			$parameters['type'],
			$parameters['action'],
		);
		return $parameters;
	}
}
