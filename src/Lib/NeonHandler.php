<?php

declare(strict_types = 1);

namespace Webovac\Core\Lib;

use App\Model\DataModel;
use App\Model\Orm;
use Nette\Neon\Neon;
use Nextras\Migrations\Entities\File;
use Nextras\Migrations\IExtensionHandler;
use Stepapo\Utils\ConfigProcessor;
use Webovac\Core\Definition\Definition;
use Webovac\Core\Definition\DefinitionProcessor;
use Webovac\Core\Definition\Manipulation;
use Webovac\Core\ManipulationGroup;


/**
 * @author Jan Tvrdík
 */
class NeonHandler implements IExtensionHandler
{
	public function __construct(
		private array $params,
		private bool $debugMode,
		private DataModel $dataModel,
		private Orm $orm,
		private DefinitionProcessor $structureProcessor,
	) {}


	public function execute(File $file): int
	{
		$count = 0;
		if (str_contains($file->name, 'dev') && !$this->debugMode) {
			return $count;
		}
		if ($file->group->migrationGroup instanceof ManipulationGroup) {
			$skipDefaults = str_contains($file->name, 'update');
			$config = (array) Neon::decode(@file_get_contents($file->path));
			$config = ConfigProcessor::process($config, $this->params);
			$repositoryName = $file->group->name;
			$repository = $this->orm->getRepositoryByName($repositoryName . 'Repository');
			if (isset($config['class'], $config['items'])) {
				$manipulationData = Manipulation::createFromArray($config, skipDefaults: $skipDefaults);
				foreach ($manipulationData->items as $itemData) {
					$repository->createFromData($itemData, skipDefaults: $skipDefaults, getOriginalByData: true);
					$count++;
				}
			} else {
				$data = $file->group->migrationGroup->class::createFromNeon($file->path, $this->params, $skipDefaults);
				$entity = $repository->createFromData($data, skipDefaults: $skipDefaults, getOriginalByData: true);
				if (method_exists($repository, 'postProcessFromData')) {
					$repository->postProcessFromData($data, $entity, skipDefaults: $skipDefaults);
				}
				$count++;
			}
		} else {
			$count = $this->structureProcessor->process(Definition::createFromNeon($file->path, $this->params));
		}
		return $count;
	}
}
