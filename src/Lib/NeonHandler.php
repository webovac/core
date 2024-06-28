<?php

declare(strict_types = 1);

namespace Webovac\Core\Lib;

use App\Model\DataModel;
use App\Model\Orm;
use App\Model\Text\TextData;
use Nette\Neon\Neon;
use Nextras\Migrations\Entities\File;
use Nextras\Migrations\IExtensionHandler;
use Stepapo\Dataset\Utils;
use Webovac\Core\Definition\Definition;
use Webovac\Core\Definition\DefinitionProcessor;
use Webovac\Core\ManipulationGroup;
use Webovac\Core\Model\CmsDataRepository;


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
			$mode = str_contains($file->name, 'update') ? CmsDataRepository::MODE_UPDATE : CmsDataRepository::MODE_INSTALL;
			$config = (array) Neon::decode(@file_get_contents($file->path));
			$parsedConfig = Utils::replaceParams($config, $this->params);
			$repositoryName = $file->group->name;
			if ($repositoryName === 'text') {
				foreach ($parsedConfig as $key => $value) {
					$translations = [];
					foreach ($value as $lang => $string) {
						$translations[$lang] = ['string' => $string];
					}
					$data = TextData::createFromArray(['name' => $key, 'translations' => $translations], $mode);
					$this->orm->textRepository->createFromData($data, mode: $mode, getOriginalByData: true);
					$count++;
				}
			} else {
				$data = $file->group->migrationGroup->class::createFromNeon($file->path, $this->params, $mode);
				$repository = $this->orm->getRepositoryByName($repositoryName . 'Repository');
				$entity = $repository->createFromData($data, mode: $mode, getOriginalByData: true);
				if (method_exists($repository, 'postProcessFromData')) {
					$repository->postProcessFromData($data, $entity, mode: $mode);
				}
				$count++;
			}
		} else {
			$count = $this->structureProcessor->process(Definition::createFromNeon($file->path, $this->params));
		}
		return $count;
	}
}
