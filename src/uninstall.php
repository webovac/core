<?php

namespace Webovac\Core;

if (@!include __DIR__ . '/../../../../vendor/autoload.php') {
	fwrite(STDERR, "Install packages using Composer.\n");
	exit(1);
}

use Webovac\Generator\CmsGenerator;


(new CmsGenerator)->uninstallModule('core');
