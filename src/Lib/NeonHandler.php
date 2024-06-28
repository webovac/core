<?php

declare(strict_types = 1);

namespace Webovac\Core\Lib;

use App\Model\DataModel;
use Nette\Neon\Neon;
use Nextras\Migrations\Entities\File;
use Nextras\Migrations\IExtensionHandler;
use Stepapo\Dataset\Utils;
use Webovac\Core\Definition\Definition;
use Webovac\Core\Definition\DefinitionProcessor;


/**
 * @author Jan Tvrdík
 */
class NeonHandler implements IExtensionHandler
{
	public function __construct(
		private array $params,
		private bool $debugMode,
		private DataModel $dataModel,
		private DefinitionProcessor $structureProcessor,
	) {}


	public function execute(File $file): int
	{
		$count = 0;
		$text = str_replace(["{$file->group->name}.", ".neon"], "", $file->name);
		if ($text === 'dev' && !$this->debugMode) {
			return $count;
		}
		$config = (array) Neon::decode(@file_get_contents($file->path));
		$parsedConfig = Utils::replaceParams($config, $this->params);
		if (in_array($file->group->mode, ['install', 'update'], true)) {
			$repository = str_replace(['-install', '-update'], '', $file->group->name);
			if ($repository === 'text') {
				foreach ($parsedConfig as $key => $value) {
					$translations = [];
					foreach ($value as $lang => $string) {
						$translations[$lang] = ['string' => $string];
					}
					$this->dataModel->{$repository . 'Repository'}->createFromConfig(['name' => $key, 'translations' => $translations], $file->group->mode);
					$count++;
				}
			} else {
				$this->dataModel->{$repository . 'Repository'}->createFromConfig($parsedConfig, $file->group->mode);
				$count++;
			}
		} else {
			$count = $this->structureProcessor->process(Definition::createFromArray($parsedConfig));
		}
		return $count;
	}
}
