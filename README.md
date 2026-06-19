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

## Automatic injection on `make:filament-resource` (optional)

By default you add the trait (or base class) yourself. If you want **every newly
generated resource** to translate its labels automatically — without editing the
generated file — enable the generator integration. Generated resources keep
extending the original Filament `Resource`; the package just adds the trait.

Publish the config:

```bash
php artisan vendor:publish --tag=filament-translatable-model-labels-config
```

Then set the flag in `config/filament-translatable-model-labels.php`:

```php
return [
    'inject_trait_into_generated_resources' => true,
];
```

Now `php artisan make:filament-resource` produces, e.g.:

```php
class OrderResource extends Resource
{
    use TranslatesFilamentModelLabels;

    protected static ?string $model = Order::class;
    // ...
}
```

The flag is **off by default**, so installing the package never changes how
resources are generated until you opt in.

> Note: this hooks Filament's internal resource generator
> (`Filament\Commands\FileGenerators\Resources\ResourceClassGenerator`) via the
> container. It is a generation-time (dev) convenience and has no runtime effect.

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
