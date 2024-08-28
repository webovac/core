<?php

namespace Webovac\Core\Lib;

use Nette\Utils\Strings;


class MonthNameProvider
{
	public function __construct(
		private DataProvider $dataProvider
	) {}


	public function getNames(string $pattern = 'MMM'): array
	{
		$months = [];
		for ($i = 1; $i <= 12; $i++) {
			$date = (new \DateTime())->setDate(year: 0, month: $i, day: 1);
			$formatter = new \IntlDateFormatter($this->dataProvider->getLanguageData()->shortcut, pattern: $pattern);
			$months[$i] = Strings::firstUpper($formatter->format($date));
		}
		return $months;
	}
}