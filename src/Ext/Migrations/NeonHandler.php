<?php

declare(strict_types = 1);

namespace Nextras\Migrations\Extensions;

use App\Model\DataModel;
use Nette\Neon\Neon;
use Nextras\Migrations\Entities\File;
use Nextras\Migrations\IExtensionHandler;
use Stepapo\Dataset\Utils;
use Tracy\Dumper;


/**
 * @author Jan Tvrdík
 */
class NeonHandler implements IExtensionHandler
{
	public function __construct(
		private array $params,
		private bool $debugMode,
		private DataModel $dataModel,
	) {}

	public function execute(File $file): int
	{
		$count = 0;
		$text = str_replace(["{$file->group->name}.", ".neon"], "", $file->name);
		if ($text === 'dev' && !$this->debugMode) {
			return $count;
		}

		$config = (array) Neon::decode(@file_get_contents($file->path));
		if ($text !== 'all') {
			//$this->printer->printText("- " . $text);
		}
		$parsedConfig = Utils::replaceParams($config, $this->params);
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
		return $count;
	}
}
