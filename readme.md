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
        driver: # mysql|pgsql
        database: # webovac
        username: # webovac_user
        password: # webovac_password
```

## Usage

Let's have a database table with books represented by Book entity

```php
/**
 * @property int $id {primary}
 * @property string $title 
 * @property Web $web {m:1 Web::$books} 
 * @property OneHasMany|BookTranslation[] $translations {1:m BookTranslation::$book, orderBy=language->rank}
 */
class Book extends CmsEntity
{
}
```

### Interfaces

#### Linkable

Let's have a page with book detail defined in Books module.

```php
class Book extends CmsEntity implements Linkable
{
    use LinkableTrait;
    
    /**
     * Specify a custom identifying columns, unless you want to use just primary key,
     * which is used in LinkableTrait::getParameters by default
     */ 
    public function getParameters(): array
    {
        return [$this->getPageName() => $this->slug];
    }

    public function getPageName(): string
    {
        return 'Books:BookDetail';
    }
}
```

```php
class BookRepository extends CmsRepository
{
    /**
     * Specify a custom filter to get book, unless you want to use just primary key,
     * which is used in CmsRepository::getByParameters by default
     */ 
    public function getByParameters(?array $parameters = null, ?string $path = null, ?WebData $webData = null): ?Book
    {
        return $this->getBy([
            'web->id' => $webData->id,
            'slug' => $parameters['Books:BookDetail'],
        ]);
    }
}
```

```html
{varType App\Model\Book\Book $book}
<a href="{$book->getLink($presenter)}">{$book->title}</a>
```

#### Renderable

Let's have books rendered by BookItemControl component created by IBookItemControl factory in Books module.

```php
class Book extends CmsEntity implements Renderable
{    
    use RenderableTrait;
	
    #[Inject] public IBookItemControl $component;
}
```

```html
{varType App\Model\Book\Book $book}
{$book->render(App\Module\Books\Books::class, 'default')}
```

#### HasRequirements

Let's have pages that utilize BookRepository and custom tag. If requirements are defined, they are checked automatically when accessing the page.

```php
class Book extends CmsEntity implements HasRequirements
{
    public function checkRequirements(CmsUser $user, WebData $webData, ?string $tag = null): bool
    {
        if ($book->web->id !== $webData->id) {
            return false;
        }
        return match ($tag) {
            null => false,
            'read' => $this->isReadableByUser($user), # custom implementation
            'update' => $this->isUpdatableByUser($user), # custom implementation
            'remove' => $this->isRemovableByUser($user), # custom implementation
		};
    }
}
```

#### HasTranslations

Let's have book translations represented by BookTranslation entity.

```php
class Book implements HasTranslations
{
    public function getTranslation(LanguageData $languageData): ?BookTranslation
    {
        return $this->translations->toCollection()->getBy(['language' => $languageData->id]);
    }
}
```
