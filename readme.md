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
        schemas: [public, library]
```

## Usage

Let's have a database table with books and their translations represented by Book and BookTranslation entity in Library module.

- `App/Module/Library/config/definitions/library.neon`

```neon
schemas:
    library:
        tables:
            book:
                columns:
                    id: [type: int, null: false, auto: true]
                    web_id: [type: int, null: false]
                indexes: [web_id]
                foreignKeys:
                    web_id: [table: web, column: id, reverseName: books]
            book_translation:
                columns:
                    id: [type: int, null: false, auto: true]
                    title: [type: string, null: false]
                    language_id: [type: int, null: false]
                    book_id: [type: int, null: false]
                indexes: [language_id, book_id]
                foreignKeys:
                    language_id: [table: language, column: id]
                    book_id: [schema: library, table: book, column: id, reverseName: translations, reverseOrder: language->rank]
```

- `App/Module/Library/config/files/library.neon`

```neon
modules:
    Library:
        components:
            BookItem: [entity: Build\Model\Book\Book, withTemplateName: true]
        entities:
            Book:
            BookTranslation:
```

```bash
php bin/generate.php
```

This will generate following files:

```php
class BookItemControl extends BaseControl
{
    public const string TEMPLATE_DEFAULT = 'default';

    public function __construct(
        private Book $book,
        private string $moduleClass,
        private string $templateName,
    ) {}

    public function render(): void
    {
        $this->template->book = $this->book;
        $this->template->renderFile($this->moduleClass, self::class, $this->templateName);
    }
}

interface IBookItemControl extends Factory
{
    function create(
        Book $book,
        string $moduleClass = Library::class,
        string $templateName = BookItemControl::TEMPLATE_DEFAULT,
    ): BookItemControl;
}

class BookItemTemplate extends BaseTemplate
{
    public Book $book;
}

/**
 * @property int $id {primary}
 * @property Web $web {m:1 Web::$books} 
 * @property OneHasMany|BookTranslation[] $translations {1:m BookTranslation::$book, orderBy=language->rank}
 */
class Book extends CmsEntity
{
    use LibraryBook;
}

/**
 * @property int $id {primary}
 * @property string $title
 * @property Language $language {m:1 Language, oneSided=true} 
 * @property Book $book {m:1 Book::$translations}
 */
class BookTranslation extends CmsEntity
{
    use LibraryBookTranslation;
}

trait LibraryBook
{
}

trait LibraryBookRepository
{
}
```

### Interfaces

- `App/Module/Library/config/files/book.neon`

```neon
modules:
    Library:
        components:
            BookItem: [entity: Build\Model\Book\Book, withTemplateName: true]
        entities:
            Book:
                entityImplements:
                    Webovac\Core\Model\Linkable:
                    Webovac\Core\Model\Renderable:
                    Webovac\Core\Model\HasWeb:
                    Webovac\Core\Model\HasRequirements:
                    Webovac\Core\Model\HasTranslations:
                repositoryImplements:
                    Webovac\Core\Model\HasWebFilter:
                    Webovac\Core\Model\HasRequirementFilter:
            BookTranslation:
                entityImplements:
                    Webovac\Core\Model\Translation:
```

```bash
php bin/generate.php
```

This will add listed interfaces to Book and BookTranslation entity and repository classes. You will implement the interfaces in LibraryBook and LibraryBookTranslation traits.

#### Linkable

Allows to easily create links from entity to detail page.

```php
trait LibraryBook
{
    use LinkableTrait;

    public function getPageName(): string
    {
        return 'Library:BookDetail';
    }
}

trait LibraryBookRepository
{
    /**
     * Specify a custom parameter to get book, unless you want to use just primary key,
     * which is used in CmsRepository::getKeyParameter by default
     */ 
    public function getKeyParameter(): string
    {
        return 'slug';
    }
}
```

```html
{varType Build\Model\Book\Book $book}
<a href="{$book->getLink($presenter)}">{$book->title}</a>
```

```php
/** @var Book $book */
$book->redirectToDetail($this->presenter);
```

#### Renderable

BookItemControl component created by IBookItemControl factory in Library module can be utilized in entity.
                    
```php
trait LibraryBook
{    
    use RenderableTrait;
    
    #[Inject] public IBookItemControl $component;
    
    public function getModuleClass(): string
    {
        return Library::class;
    }
}
```

```latte
{varType Build\Model\Book\Book $book}
{$book} {* renders book title *}
{$book->render()} {* renders BookItemControl template default.latte *}
{$book->render('row')} {* renders BookItemControl template row.latte *}
```

#### HasWeb

CMS prevents users from accessing entities from websites other than the website entity is connected to.

```php
trait LibraryBook
{
    use HasWebTrait;
}

trait LibraryBookRepository
{
    use HasWebFilterTrait;
}
```

#### HasRequirements

If requirements are defined, they are checked automatically when accessing the page.

```php
trait LibraryBook
{
    public function checkRequirements(CmsUser $user, ?string $tag = null): bool
    {
        return match ($tag) {
            null => false,
            'read' => $this->isReadableByUser($user), # custom implementation
            'update' => $this->isUpdatableByUser($user), # custom implementation
            'remove' => $this->isRemovableByUser($user), # custom implementation
        };
    }
}

trait LibraryBookRepository
{
    public function getRequirementFilter(CmsUser $user): bool
    {
        return [
            # custom implementation of filter that is called to find books readable to user
        ]
    }
}
```

#### HasTranslations

Virtual title property can be defined in LibraryBook trait that shows title of current language translation.

```php
/**
 * @property string $title {virtual} 
 */
trait LibraryBook
{
    #[Inject] public DataProvider $dataProvider;
    
    public function getTranslation(LanguageData $languageData): ?BookTranslation
    {
        return $this->translations->toCollection()->getBy(['language' => $languageData->id]);
    }
    
    public function getterTitle(): string
    {
        return $this->getTranslation($this->dataProvider->getLanguageData())->title;
    }
}
```
