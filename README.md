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

## How it works

- The singular label is `__(<humanised model name>)` — e.g. `Issue` → `__('issue')`.
- The plural label is `__(Str::plural(<humanised model name>))` — e.g. `__('issues')`.
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
