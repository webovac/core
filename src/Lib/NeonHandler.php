<?php

declare(strict_types = 1);

namespace Webovac\Core\Lib;

use App\Model\Orm;
use Nette\Neon\Neon;
use Nextras\Migrations\Entities\File;
use Nextras\Migrations\IExtensionHandler;
use Stepapo\Model\Definition\Config\Definition;
use Stepapo\Model\Definition\DbProcessor;
use Stepapo\Utils\ConfigProcessor;
use Stepapo\Utils\Service;
use Webovac\Core\Definition\Manipulation;
use Webovac\Core\ManipulationGroup;


/**
 * @author Jan Tvrdík
 */
class NeonHandler implements IExtensionHandler, Service
{
	public function __construct(
		private array $params,
		private bool $debugMode,
		private bool $testMode,
		private Orm $orm,
		private DbProcessor $processor,
	) {}


	public function execute(File $file): int
	{
		$count = 0;
		if ($file->group->migrationGroup instanceof ManipulationGroup) {
			$skipDefaults = str_contains($file->name, 'update');
			$config = (array) Neon::decode(@file_get_contents($file->path));
			$config = ConfigProcessor::process($config, $this->params);
			$repositoryName = $file->group->name;
			$repository = $this->orm->getRepositoryByName($repositoryName . 'Repository');
			if (isset($config['class'], $config['items'])) {
				$manipulationData = Manipulation::createFromArray($config, skipDefaults: $skipDefaults);
				$prodMode = !$this->debugMode && !$this->testMode;
				if (
					($prodMode && !in_array('prod', $manipulationData->modes, true))
					|| ($this->debugMode && !in_array('dev', $manipulationData->modes, true))
					|| ($this->testMode && !in_array('test', $manipulationData->modes, true))
				) {
					return $count;
				}
				foreach ($manipulationData->items as $itemData) {
					$entity = $repository->createFromData($itemData, skipDefaults: $skipDefaults, getOriginalByData: true);
					if (method_exists($repository, 'postProcessFromData')) {
						$repository->postProcessFromData($itemData, $entity, skipDefaults: $skipDefaults);
					}
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
//			$count = $this->processor->process(Definition::createFromNeon($file->path, $this->params));
		}
		return $count;
	}
}
