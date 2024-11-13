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
    webovac.core: Webovac\Core\DI\CoreExtension

webovac.core:
    host: # example.com
    db:
        driver: # mysql|pqsql
        database: # webovac
        username: # webovac_user
        password: # webovac_password
```

## Usage

Let's have a database table with books represented by Book entity

### Linkable

Let's have a page with book detail defined in Books module.

```php
/**
 * @property string $title 
 */
class Book extends CmsEntity implements Linkable
{
    use LinkableTrait;
    
    public function getParameters(): array
    {
        return [$this->getPageName() => $this->id];
    }

    public function getPageName(): string
    {
        return 'Books:BookDetail';
    }
}
```

```html
{varType App\Model\Book\Book $book}
<a href="{$book->getLink($presenter)}">{$book->title}</a>
```

### Renderable

Let's have books rendered by BookItemControl component in Books module.

```php
class Book extends CmsEntity implements Renderable
{    
    private IBookItemControl $component;

    public function injectComponent(IBookItemControl $component): void
    {
        $this->component = $component;
    }
    
    public function getComponent(string $moduleClass, string $templateName): BookItemControl
    {
        return $this->component->create($this, $moduleClass, $templateName);
    }
}
```

### HasRequirements

Let's have pages that utilize BookRepository and custom tag.

```php
/**
 * @property Web $web {m:1 Web::$books} 
 */
class Book extends CmsEntity implements HasRequirements
{
    public function checkRequirements(CmsUser $user, WebData $webData, ?string $tag = null): bool
    {
        if ($book->web->id !== $webData->id) {
            return false;
        }
        return match ($tag) {
            'read' => $this->isReadableByUser($user), # custom implementation
            'update' => $this->isUpdatableByUser($user), # custom implementation
            'remove' => $this->isRemovableByUser($user), # custom implementation
        }
        return false;
    }
}
```

### HasTranslations

Let's have book translations represented by BookTranslation entity.

```php
/**
 * @property OneHasMany|BookTranslation[] $translations {1:m BookTranslation::$book, orderBy=language->rank}
 */
class Book implements HasTranslations
{
    public function getTranslation(LanguageData $languageData): ?BookTranslation
    {
        return $this->translations->toCollection()->getBy(['language' => $languageData->id]);
    }
}
```
