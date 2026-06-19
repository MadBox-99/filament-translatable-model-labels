# Filament Translatable Model Labels

Resolve Filament Resource model labels (singular **and** plural) — and therefore
the navigation item and table heading — through Laravel's `__()` translation,
keyed off the auto-derived model name. Define the translation once; every
Resource picks it up without per-Resource overrides.

## Installation

```bash
composer require madbox-99/filament-translatable-model-labels
```

## Usage

Apply the trait to a shared base Resource so it is never repeated:

```php
use Filament\Resources\Resource;
use MadBox99\FilamentTranslatableModelLabels\Concerns\TranslatesFilamentModelLabels;

abstract class BaseResource extends Resource
{
    use TranslatesFilamentModelLabels;
}
```

Or extend the provided base class:

```php
use MadBox99\FilamentTranslatableModelLabels\TranslatableFilamentResource;

class IssueResource extends TranslatableFilamentResource
{
    protected static ?string $model = \App\Models\Issue::class;
}
```

Add the translations to your app's `lang/hu.json`:

```json
{
    "issue": "probléma",
    "issues": "problémák"
}
```

Result: the create button reads "Új probléma", and the navigation item and table
heading read "Problémák".

## Automatic injection on `make:filament-resource`

This works out of the box — **no configuration needed**. Once the package is
installed, `php artisan make:filament-resource` adds the trait to every generated
resource automatically. The generated class still extends the original Filament
`Resource`; the package just adds the trait:

```php
class OrderResource extends Resource
{
    use TranslatesFilamentModelLabels;

    protected static ?string $model = Order::class;
    // ...
}
```

The trait is a no-op when no translation exists (it returns the stock label), so
it is harmless even on resources you don't translate.

### Turning it off

If you'd rather add the trait manually, publish the config and disable it:

```bash
php artisan vendor:publish --tag=filament-translatable-model-labels-config
```

```php
// config/filament-translatable-model-labels.php
return [
    'inject_trait_into_generated_resources' => false,
];
```

> Note: this hooks Filament's internal resource generator
> (`Filament\Commands\FileGenerators\Resources\ResourceClassGenerator`) via the
> container. It is a generation-time (dev) convenience with no runtime effect.

## Retrofitting existing resources (Rector)

The generator integration only affects **newly** generated resources. To add the
trait to all of your **existing** resources in one pass, use the bundled
[Rector](https://github.com/rectorphp/rector) rule.

Add it to your `rector.php`:

```php
use MadBox99\FilamentTranslatableModelLabels\Rector\AddTranslatesFilamentModelLabelsTraitRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([__DIR__ . '/app'])
    ->withImportNames()
    ->withRules([
        AddTranslatesFilamentModelLabelsTraitRector::class,
    ]);
```

Then run it:

```bash
vendor/bin/rector
```

Every class that extends `Filament\Resources\Resource` and does not already use
the trait gets `use TranslatesFilamentModelLabels;` added. Resources that already
use it, and non-resource classes, are left untouched. Requires `rector/rector` in
your project.

## How it works

- The singular label is `__(<humanised model name>)` — e.g. `Issue` → `__('issue')`.
- The plural label is `__(locale_has_pluralization() ? Str::plural(<humanised model name>) : <humanised model name>)` — uses the pluralized form when the active locale supports pluralization, otherwise the singular form.
- An explicit `$modelLabel` / `$pluralModelLabel` on the Resource still wins.
- If no translation exists, `__()` returns the key unchanged, so untranslated
  locales behave exactly like stock Filament.

### Multi-word models

The key is the humanised, lower-cased model name with spaces, e.g. `BlogPost`
becomes `__('blog post')` / `__('blog posts')`:

```json
{
    "blog post": "blogbejegyzés",
    "blog posts": "blogbejegyzések"
}
```

## Testing

```bash
composer test
```

## License

MIT.
