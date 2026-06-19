# Translatable model labels for Filament — reusable package

**Date:** 2026-06-19
**Status:** Design — approved direction (reusable package)
**Package:** `madbox-99/filament-translatable-model-labels` (public, Packagist)
**Namespace:** `MadBox99\FilamentTranslatableModelLabels`
**Targets:** Filament v5 (`filament/filament`), PHP 8.4, Laravel 13

## Problem

Filament derives a Resource's singular and plural labels from the model class
name (`get_model_label()` → `Str::plural()`). To localise them, each Resource
must override `getModelLabel()` / `getPluralModelLabel()` or set `$modelLabel` /
`$pluralModelLabel` by hand. This is repetitive across many Resources and the
singular/plural handling has to be restated per Resource.

We want labels to resolve through Laravel's `__()` translation, keyed off the
auto-derived model name, so one `lang/hu.json` entry localises a Resource
everywhere it appears — the "New X" button, the navigation item, and the table
heading.

Example: with `{"issue": "probléma", "issues": "problémák"}` in `hu.json`,
"New issue" → "Új probléma" and the "Issues" navigation/table heading → "Problémák".

## Why a package (not a core PR)

A core PR was investigated and rejected as a direction. Prior maintainer
decisions on the exact idea:

- **PR [#15975](https://github.com/filamentphp/filament/pull/15975)** (closed) —
  adding a `translateLabel()` to more surfaces. danharrin: *"I do not want this
  feature in Filament either. I wish I never allowed `translateLabel()` into
  Filament in the first place for forms."*
- **Issue [#9682](https://github.com/filamentphp/filament/issues/9682)** (closed)
  — request for a toggle to disable label auto-casing; resisted by maintainers.
- **PR [#8957](https://github.com/filamentphp/filament/pull/8957)** (closed) —
  translating the plural resource name; maintainers' answer was "override
  `getPluralModelLabel()` and pass the actual plural."

The official maintainer guidance is therefore to override the label methods in
your own code. This package packages exactly that override so it is written once
and reused across projects, without depending on framework acceptance.

A classic Filament `Plugin` object is **not** the right mechanism: a plugin
cannot override the static label methods of arbitrary Resources. A trait
(applied to a Resource or a shared base Resource) is the correct tool, and is the
opt-in itself.

## Design

### 1. Trait `TranslatesModelLabels`

Using the trait is the opt-in, so no extra toggle is needed (simpler than a core
version would be).

```php
namespace MadBox99\FilamentTranslatableModelLabels\Concerns;

use Illuminate\Support\Str;
use function Filament\Support\get_model_label;
use function Filament\Support\locale_has_pluralization;

trait TranslatesModelLabels
{
    public static function getModelLabel(): string
    {
        return static::$modelLabel ?? __(get_model_label(static::getModel()));   // __('issue')
    }

    public static function getPluralModelLabel(): string
    {
        if (filled($label = static::$pluralModelLabel)) {
            return $label;                                                        // explicit override wins
        }

        $base = static::$modelLabel ?? get_model_label(static::getModel());       // "issue"

        return __(locale_has_pluralization() ? Str::plural($base) : $base);       // __('issues')
    }
}
```

Notes:
- `Filament\Support\get_model_label()` and `locale_has_pluralization()` are
  public helper functions in `filament/support` — a stable dependency.
- `$modelLabel` / `$pluralModelLabel` are the existing Resource properties
  (from Filament's `HasLabels`), so a consumer's explicit override still wins.
- The plural key is computed from the **untranslated** singular, then translated,
  so the lookup is `__('issues')` rather than pluralising a translated string.
- `getTitleCaseModelLabel()` / `getTitleCasePluralModelLabel()` are inherited
  unchanged, so navigation labels and the list-page table heading follow
  automatically — one trait covers all three surfaces.

### 2. Optional base class `TranslatableResource`

For consumers who prefer inheritance over a `use` statement:

```php
namespace MadBox99\FilamentTranslatableModelLabels;

use Filament\Resources\Resource;
use MadBox99\FilamentTranslatableModelLabels\Concerns\TranslatesModelLabels;

abstract class TranslatableResource extends Resource
{
    use TranslatesModelLabels;
}
```

### 3. ServiceProvider

Minimal Laravel package provider for auto-discovery (and a home for any future
config). No config is required for the core behaviour.

```php
namespace MadBox99\FilamentTranslatableModelLabels;

use Illuminate\Support\ServiceProvider;

class FilamentTranslatableModelLabelsServiceProvider extends ServiceProvider
{
    public function register(): void {}
    public function boot(): void {}
}
```

Registered via composer `extra.laravel.providers` for package auto-discovery.

## Usage (consumer side)

Apply once on a shared base Resource so it is never repeated per Resource:

```php
// app/Filament/Resources/Resource.php (the project's shared base)
abstract class Resource extends \Filament\Resources\Resource
{
    use \MadBox99\FilamentTranslatableModelLabels\Concerns\TranslatesModelLabels;
}
```

or extend the package's base class:

```php
class IssueResource extends \MadBox99\FilamentTranslatableModelLabels\TranslatableResource { ... }
```

Translations in the app's `lang/hu.json`:

```json
{
    "issue": "probléma",
    "issues": "problémák"
}
```

## Backwards compatibility / fallback

`__()` returns the key unchanged when no translation exists, so a Resource using
the trait in an untranslated locale (e.g. English) produces output identical to
stock Filament (`__('issue')` → `'issue'`). Only Resources that opt in are
affected.

## Known behaviour to document

- Flat, app-global keys (`__('issue')`). Documented; chosen for simplicity.
- Multi-word models produce keys with spaces: `BlogPost` → `"blog post"`,
  `"blog posts"`. Document in the README.

## Repository / package layout

```
filament-translatable-model-labels/
├── composer.json
├── README.md
├── LICENSE
├── src/
│   ├── Concerns/TranslatesModelLabels.php
│   ├── TranslatableResource.php
│   └── FilamentTranslatableModelLabelsServiceProvider.php
├── tests/
│   ├── Pest.php
│   ├── TestCase.php
│   └── Feature/TranslatesModelLabelsTest.php
├── docs/                       (this spec)
└── .github/workflows/tests.yml (CI: Pest + Pint)
```

`composer.json` essentials:
- `name`: `madbox-99/filament-translatable-model-labels`
- `require`: `php: ^8.3`, `filament/filament: ^5.0`
- `require-dev`: `pestphp/pest`, `orchestra/testbench`, `laravel/pint`
- `autoload` PSR-4: `MadBox99\\FilamentTranslatableModelLabels\\` → `src/`
- `extra.laravel.providers`: the ServiceProvider

## Tests (Pest, via Orchestra Testbench)

1. Resource using the trait returns the translated singular when the key exists.
2. Returns the translated plural (`__('issues')`) when the key exists.
3. Falls back to the stock humanised label when the key is missing.
4. An explicit `$modelLabel` / `$pluralModelLabel` still wins over translation.
5. The rendered list-page heading / navigation label reflects the translation.

## Publishing

1. Build the package in this repo (`MadBox-99/filament-translatable-model-labels`).
2. Green CI (Pest + Pint).
3. Make the repo public, tag `v1.0.0`.
4. Submit to Packagist; enable the auto-update webhook.

## Out of scope (YAGNI)

- A core PR (rejected direction — see above).
- A namespaced key convention or configurable key strategy (revisit only on
  demand).
- A Model-side trait/interface for the label.
- A panel-level global switch.
