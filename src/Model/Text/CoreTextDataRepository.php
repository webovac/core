<?php

declare(strict_types=1);

namespace Webovac\Core\Model\Text;

use App\Model\TextTranslation\TextTranslationDataRepository;
use Nette\DI\Attributes\Inject;


trait CoreTextDataRepository
{
	#[Inject] public TextTranslationDataRepository $textTranslationDataRepository;
}