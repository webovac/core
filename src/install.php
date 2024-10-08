<?php

declare(strict_types=1);

namespace Webovac\Core;

if (@!include __DIR__ . '/../../../../vendor/autoload.php') {
	fwrite(STDERR, "Install packages using Composer.\n");
	exit(1);
}

use Webovac\Core\Model\HasParent;
use Webovac\Core\Model\HasTranslations;
use Webovac\Core\Model\Linkable;
use Webovac\Core\Model\Page\HasPages;
use Webovac\Core\Model\Translation;
use Webovac\Generator\CmsGenerator;


(new CmsGenerator)->installModule(
	name: 'core',
	entities: [
		'Log',
		'Person',
		'File',
		'Language' => [Linkable::class, HasTranslations::class],
		'LanguageTranslation' => [Translation::class],
		'Module' => [HasPages::class, HasTranslations::class],
		'ModuleTranslation' => [Translation::class],
		'Page' => [Linkable::class, HasPages::class, HasTranslations::class],
		'PageTranslation' => [Translation::class],
		'Preference',
		'Parameter',
		'Role',
		'Signal',
		'Text' => [HasTranslations::class],
		'TextTranslation' => [Translation::class],
		'Web' => [Linkable::class, HasPages::class, HasTranslations::class],
		'WebTranslation' => [Translation::class],
	],
);
