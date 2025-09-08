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
use Stepapo\Restful\Application\BadRequestException;
use Stepapo\Restful\Application\UI\ResourcePresenter;
use Stepapo\Restful\ConvertedResource;
use Stepapo\Restful\Converters\DateTimeConverter;
use Stepapo\Restful\Converters\ResourceConverter;
use Stepapo\Restful\IResource;
use Stepapo\Restful\Resource;
use Webovac\Core\Lib\DataProvider;


class ApiPresenter extends ResourcePresenter
{
	public ?string $apiKey = null;

	public ?IEntity $item = null;

	private const array TYPE_MAP = [
		'json' => IResource::JSON,
		'xml' => IResource::XML,
	];

	private string $type;


	public function __construct(
		private Orm $orm,
//		protected OAuth2Authentication $authenticationProcess,
		private ResourceGenerator $resourceGenerator,
		private DataProvider $dataProvider,
		private DataModel $dataModel,
	) {
		parent::__construct();
	}


	public function startup()
	{
		parent::startup();
		$this->dataProvider->setLanguageData($this->dataModel->getLanguageDataByShortcut($this->getParameter('lang') ?: 'cs'));
		$type = $this->getParameter('type') ?: 'json';
		if (!isset(self::TYPE_MAP[$type])) {
			$this->sendErrorResource(BadRequestException::notFound("Type '$type' not supported."), IResource::JSON);
		}
		$this->type = self::TYPE_MAP[$type];
//		if (!$apiKey = $this->getParameter('apiKey')) {
//			$this->sendErrorResource(BadRequestException::unauthorized('Missing API key.'), $this->typeMap[$type]);
//		}
//		if (!$this->loggedPerson = $this->orm->personRepository->getBy(['apiKey' => $apiKey])) {
//			$this->sendErrorResource(BadRequestException::unauthorized('Unrecognized API key.'), $this->typeMap[$type]);
//		}
		if (!$entity = $this->getParameter('entity')) {
			$this->sendErrorResource(BadRequestException::notFound('No entity.'), $this->type);
		}
		if ($id = $this->getParameter('id')) {
			$this->item = $this->getRepository()->getById($id);
			if (!$this->item) {
				$this->sendErrorResource(BadRequestException::notFound("No entity '$entity' with ID '$id' found."), $this->type);
			}
		}
	}


	private function getRepository(): IRepository
	{
		try {
			$entity = $this->getParameter('entity');
			$repository = $this->orm->getRepositoryByName($entity . 'Repository');
		} catch (\Exception $e) {
			$this->sendErrorResource(BadRequestException::notFound("Entity '$entity' not found."), $this->type);
		}
		return $repository;
	}


	public function actionRead(string $entity, ?int $id = null, ?string $related = null, string $type = 'json')
	{
		$queryParameters = $this->getParameters();
		unset(
			$queryParameters['entity'],
			$queryParameters['id'],
			$queryParameters['related'],
			$queryParameters['type'],
			$queryParameters['action'],
		);
		if ($this->item) {
			if ($related) {
				try {
					if (!$this->item->{$related} instanceOf IRelationshipCollection) {
						throw new InvalidArgumentException("'$related' is not a collection.");
					}
					$this->item->getMetadata()->getProperty($related)->relationship->repository;
					$this->resource = $this->resourceGenerator->createFromArrayQuery(
						$this->item->getProperty($related)->toCollection(),
						$queryParameters
					);
				} catch (InvalidArgumentException $e) {
					$this->sendErrorResource(BadRequestException::unprocessableEntity([], $e->getMessage()), $this->type);
				} catch (ValidationException $e) {
					$this->sendErrorResource(BadRequestException::unprocessableEntity([], $e->getMessage()), $this->type);
				}
			} else {
				$this->resource = $this->resourceGenerator->create($this->item);
			}
		} else {
			try {
				$this->resource = $this->resourceGenerator->createFromArrayQuery(
					$this->getRepository()->findAll(),
					$queryParameters
				);
			} catch (BadRequestException $e) {
				$this->sendErrorResource(BadRequestException::unprocessableEntity([], $e->getMessage()), $this->type);
			} catch (ValidationException $e) {
				$this->sendErrorResource(BadRequestException::unprocessableEntity([], $e->getMessage()), $this->type);
			}
		}
		$this->sendResource($this->type);
	}
}