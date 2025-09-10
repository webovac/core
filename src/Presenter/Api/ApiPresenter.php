<?php

declare(strict_types=1);

namespace Webovac\Core\Presenter\Api;

use App\Lib\ResourceGenerator\ToArrayConverterWithoutMany;
use App\Lib\ResourceGenerator\ResourceGenerator;
use App\Model\DataModel;
use App\Model\Orm;
use Nette\DI\Attributes\Inject;
use Nette\InvalidArgumentException;
use Nette\Schema\ValidationException;
use Nextras\Orm\Collection\ICollection;
use Nextras\Orm\Entity\IEntity;
use Nextras\Orm\Relationships\IRelationshipCollection;
use Nextras\Orm\Repository\IRepository;
use ReflectionClass;
use Stepapo\Restful\Application\BadRequestException;
use Stepapo\Restful\Application\UI\ResourcePresenter;
use Stepapo\Restful\Resource;
use Stepapo\Restful\Security\Process\OAuth2Authentication;
use Webovac\Core\Lib\DataProvider;
use Webovac\Core\Model\PrivateRepository;


class ApiPresenter extends ResourcePresenter
{
	private ?string $entityName;
	public ?IEntity $item = null;


	public function __construct(
		private Orm $orm,
		private OAuth2Authentication $authenticationProcess,
		private ResourceGenerator $resourceGenerator,
		private DataProvider $dataProvider,
		private DataModel $dataModel,
	) {
		parent::__construct();
	}


	public function startup()
	{
		parent::startup();
//		$this->authentication->setAuthProcess($this->authenticationProcess);
		$this->dataProvider->setLanguageData($this->dataModel->getLanguageDataByShortcut($this->getParameter('lang') ?: 'cs'));
		$this->entityName = $this->getParameter('entity');
		if (!$this->entityName) {
			$this->sendErrorResource(BadRequestException::notFound('No entity.'));
		}
		if ($id = $this->getParameter('id')) {
			$this->item = $this->getRepository()->getById($id);
			if (!$this->item) {
				$this->sendErrorResource(BadRequestException::notFound("No entity '$this->entityName' with ID '$id' found."));
			}
		}
	}


	private function getRepository(): IRepository
	{
		try {
			$repository = $this->orm->getRepositoryByName($this->entityName . 'Repository');
			if ($repository instanceof PrivateRepository) {
				throw new \InvalidArgumentException;
			}
		} catch (\Exception $e) {
			$this->sendErrorResource(BadRequestException::notFound("Entity '$this->entityName' not found."));
		}
		return $repository;
	}


	public function actionRead(string $entity, ?int $id = null, ?string $related = null, string $type = 'json')
	{
		if ($this->item) {
			if ($related) {
				try {
					$rc = new ReflectionClass($this->item->getMetadata()->getProperty($related)->relationship->repository);
					if (
						!$this->item->{$related} instanceOf IRelationshipCollection
						|| $rc->implementsInterface(PrivateRepository::class)
					) {
						throw new InvalidArgumentException("'$related' is not a collection.");
					}
					$this->sendCollectionResource($this->item->getProperty($related)->toCollection());
				} catch (InvalidArgumentException $e) {
					$this->sendErrorResource(BadRequestException::notFound($e->getMessage()));
				}
			} else {
				$this->sendEntityResource($this->item);
			}
		} else {
			$this->sendCollectionResource($this->getRepository()->findAll());
		}
	}


	private function sendEntityResource(IEntity $entity)
	{
		$this->resource = $this->resourceGenerator->createFromArrayQuery(
			$entity,
			$this->getQueryParameters(),
		);
	}


	private function sendCollectionResource(ICollection $collection)
	{
		try {
			$this->resource = $this->resourceGenerator->createFromArrayQuery(
				$collection,
				$this->getQueryParameters(),
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
			$parameters['entity'],
			$parameters['id'],
			$parameters['related'],
			$parameters['type'],
			$parameters['action'],
		);
		return $parameters;
	}
}
