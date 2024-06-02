# Webovac Core

Core module for using Webovac CMS in your project.

## Instalation

Recommended way is to initialize project from [webovac/project](https://www.github.com/webovac/project). For manual installation to existing project make sure your projects follows the same folder structure.

1. composer

```bash
composer require webovac/core
```
2. config.neon

```neon
extensions:
    webovac.core: Webovac\Core\DI\CoreExtension(%rootDir%, %webovac%)

parameters:
    webovac:
        # cmsHost: cms.example.com
        # add other custom parameters such as hosts for installation of webovac web modules
```
