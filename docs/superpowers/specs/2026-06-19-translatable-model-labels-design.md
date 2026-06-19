# Translatable model labels for Filament Resources

**Date:** 2026-06-19
**Status:** Design — pending maintainer validation (GitHub Discussion + `@danharrin`)
**Target:** `filamentphp/filament` (v5), package `filament/filament`

## Problem

Filament derives a Resource's singular and plural labels from the model class
name (`get_model_label()` → `Str::plural()`). To localise these (e.g. Hungarian),
each Resource must currently override `getModelLabel()` / `getPluralModelLabel()`
or set `$modelLabel` / `$pluralModelLabel` by hand. This is repetitive and
error-prone across many Resources, and the singular/plural handling has to be
restated per Resource.

We want labels to resolve through Laravel's `__()` translation, keyed off the
auto-derived model name, so a single `lang/hu.json` entry localises a Resource's
label everywhere it appears — the "New X" button, the navigation item, and the
table heading — without per-Resource overrides.

Example: with `{"issue": "probléma", "issues": "problémák"}` in `hu.json`,
"New issue" becomes "Új probléma" and the "Issues" table/navigation becomes
"Problémák".

## Why this fits Filament (precedent)

The design mirrors two patterns that already exist in the codebase, so it is an
extension of established convention rather than a new paradigm:

1. **`translateLabel()` on components** —
   `Filament\Tables\Columns\Concerns\HasLabel` (and the equivalents in schemas,
   actions, filters, query-builder) auto-derive a label and conditionally wrap it
   in `__()`:

   ```php
   protected bool $shouldTranslateLabel = false;
   public function translateLabel(bool $shouldTranslateLabel = true): static { ... }
   public function getLabel(): string|Htmlable
   {
       $label = $this->evaluate($this->label) ?? /* derive from name */;
       return $this->shouldTranslateLabel ? __($label) : $label;
   }
   ```

2. **`titleCaseModelLabel()` on Resources** — the *same file* we are changing,
   `Filament\Resources\Resource\Concerns\HasLabels`, already uses the static
   toggle shape we will copy:

   ```php
   protected static bool $hasTitleCaseModelLabel = true;
   public static function titleCaseModelLabel(bool $condition = true): void { ... }
   public static function hasTitleCaseModelLabel(): bool { ... }
   ```

The feature is `translateLabel()`'s `__()`-wrapping applied to Resource labels,
gated by a `titleCaseModelLabel()`-style static toggle.

## Design

All changes are in
`packages/filament/src/Resources/Resource/Concerns/HasLabels.php`.

### 1. Toggle (mirrors `titleCaseModelLabel`)

```php
protected static bool $shouldTranslateModelLabel = false;

public static function translateModelLabel(bool $condition = true): void
{
    static::$shouldTranslateModelLabel = $condition;
}

public static function shouldTranslateModelLabel(): bool
{
    return static::$shouldTranslateModelLabel;
}
```

### 2. Singular label (mirrors `translateLabel`)

```php
public static function getModelLabel(): string
{
    $label = static::$modelLabel
        ?? static::getLabel()
        ?? get_model_label(static::getModel());           // "issue"

    return static::shouldTranslateModelLabel() ? __($label) : $label;   // __('issue')
}
```

### 3. Plural label

The plural key is computed from the **untranslated** singular and then
translated, so the lookup is `__('issues')` rather than pluralising an
already-translated string:

```php
public static function getPluralModelLabel(): string
{
    if (filled($label = static::$pluralModelLabel ?? static::getPluralLabel())) {
        return $label;                                    // explicit override wins, unchanged
    }

    $base = static::$modelLabel
        ?? static::getLabel()
        ?? get_model_label(static::getModel());           // "issue"

    $label = locale_has_pluralization() ? Str::plural($base) : $base;   // "issues"

    return static::shouldTranslateModelLabel() ? __($label) : $label;   // __('issues')
}
```

### 4. Untouched

`getTitleCaseModelLabel()` and `getTitleCasePluralModelLabel()` keep wrapping the
result in `Str::ucwords()`. Because navigation labels and the list-page table
heading derive from these label methods, a single toggle covers all three
surfaces (create button, navigation, table heading) — no further changes needed.

## Enabling it

- **One Resource:** set `protected static bool $shouldTranslateModelLabel = true;`
  or call `static::translateModelLabel()`.
- **All Resources at once:** set it on the app's shared base `Resource` class
  that every Resource extends. This solves the original "don't repeat per
  Resource" goal with a single declaration and needs no additional core surface
  (no panel-level change required).

## Backwards compatibility

The toggle defaults to `false`, exactly like `translateLabel()` defaults off, so
existing apps are unaffected. Even for a Resource that opts in, `__()` returns the
key unchanged when no translation exists, so an untranslated locale (e.g. English)
produces output identical to today (`__('issue')` → `'issue'`).

## Known concerns to disclose in the PR

- **Flat, app-global keys** (`__('issue')`). A maintainer may prefer a namespaced
  convention to avoid collisions. Mitigation: the behaviour is opt-in, so only
  Resources that explicitly enable it consult these keys.
- **Multi-word models** produce keys containing spaces (`BlogPost` → `"blog post"`,
  `"blog posts"`). This must be documented.

## Localisation (consumer side, the app's `lang/hu.json`)

```json
{
    "issue": "probléma",
    "issues": "problémák"
}
```

## Scope of the pull request

1. The `HasLabels` changes above.
2. Pest tests: translation on/off, and missing-key fallback, for singular, plural,
   and the rendered table heading.
3. Documentation: a "Translating model labels" section under the Resources docs.
4. `vendor/bin/pint` formatting and a green Larastan run.

## Process (per Filament contributing guide)

0. **Validate first (required):** open a GitHub Discussion on
   `filamentphp/filament`, `@danharrin`, describe the problem and the
   precedent-mirroring proposal, and confirm suitability **before** writing code.
1. Fork `filamentphp/filament`; create a local Laravel app; clone the fork into
   its root (`/filament`); add a path repository for `filament/packages/*` with
   `"minimum-stability": "dev"`; `composer update`.
2. Branch, e.g. `feat/translatable-model-labels`.
3. Implement, test, document, Pint, Larastan.
4. Open the PR referencing the Discussion.

## Out of scope (YAGNI)

- Panel-level global switch (`->translateModelLabels()`): superseded by setting
  the toggle on a shared base Resource class; revisit only if maintainers prefer
  a panel API.
- A namespaced key convention: only if requested during review.
- A Model-side trait/interface for the label: larger API change, not needed.
