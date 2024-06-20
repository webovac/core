# Webovac Core

Core module for using Webovac CMS in your project.

## Instalation

Recommended way is to initialize project from [webovac/project](https://www.github.com/webovac/project). For manual installation to existing project make sure your projects follows the same folder structure.

1. composer

```bash
composer require webovac/core
php vendor/webovac/core/src/install.php
```

2. config.neon

```neon
extensions:
    webovac.core: Webovac\Core\DI\CoreExtension

webovac.core:
    host: # example.com
    db:
        driver: # mysql|pqsql
        database: # webovac
        username: # webovac_user
        password: # webovac_password
```
