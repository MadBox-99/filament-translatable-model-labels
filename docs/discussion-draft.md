# Translatable model labels for Resources (mirroring `translateLabel()`)

**Category:** Ideas / Feature discussion
**Tag:** @danharrin — checking suitability before opening a PR, per the contributing guide.

## Problem

A Resource's singular and plural labels are derived from the model class name
(`get_model_label()` → `Str::plural()`). To localise them, every Resource has to
override `getModelLabel()` / `getPluralModelLabel()` or set `$modelLabel` /
`$pluralModelLabel` by hand. Across a panel with many Resources this is
repetitive, and the singular/plural handling gets restated per Resource.

It would be nice to resolve labels through `__()` keyed off the auto-derived
model name, so a single translation entry localises a Resource everywhere it
appears — the create button, the navigation item and the table heading — with no
per-Resource overrides.

## Proposal

Add an opt-in toggle to `Resources/Resource/Concerns/HasLabels` that, when
enabled, wraps the auto-derived label in `__()`. This mirrors two patterns that
already exist in the framework rather than introducing a new one:

1. **`translateLabel()` on components** (`Tables\Columns\Concerns\HasLabel`,
   and the schemas/actions/filters equivalents):

   ```php
   protected bool $shouldTranslateLabel = false;
   public function translateLabel(bool $shouldTranslateLabel = true): static { ... }
   public function getLabel(): string|Htmlable
   {
       $label = $this->evaluate($this->label) ?? /* derived from name */;
       return $this->shouldTranslateLabel ? __($label) : $label;
   }
   ```

2. **`titleCaseModelLabel()` in the very same `HasLabels` concern** — the static
   toggle shape this proposal copies:

   ```php
   protected static bool $hasTitleCaseModelLabel = true;
   public static function titleCaseModelLabel(bool $condition = true): void { ... }
   public static function hasTitleCaseModelLabel(): bool { ... }
   ```

### Sketch

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

public static function getModelLabel(): string
{
    $label = static::$modelLabel ?? static::getLabel() ?? get_model_label(static::getModel());

    return static::shouldTranslateModelLabel() ? __($label) : $label;          // __('issue')
}

public static function getPluralModelLabel(): string
{
    if (filled($label = static::$pluralModelLabel ?? static::getPluralLabel())) {
        return $label;
    }

    $base = static::$modelLabel ?? static::getLabel() ?? get_model_label(static::getModel());
    $label = locale_has_pluralization() ? Str::plural($base) : $base;          // "issues"

    return static::shouldTranslateModelLabel() ? __($label) : $label;          // __('issues')
}
```

`getTitleCaseModelLabel()` / `getTitleCasePluralModelLabel()` are untouched, so
navigation labels and the list-page table heading follow automatically — one
toggle covers all three surfaces.

### Usage

```php
// app/lang/hu.json
{ "issue": "probléma", "issues": "problémák" }
```

Per Resource: `protected static bool $shouldTranslateModelLabel = true;`
For a whole panel: set it once on a shared base `Resource` class.

Result: "New issue" → "Új probléma", and the "Issues" navigation/table heading
→ "Problémák".

## Backwards compatibility

Defaults to `false`, exactly like `translateLabel()`. Even when enabled, `__()`
returns the key unchanged if no translation exists, so untranslated locales
behave identically to today.

## Open questions for maintainers

1. Is this direction welcome for a PR?
2. Flat app-global keys (`__('issue')`) vs. a namespaced convention — any
   preference? (Kept flat here for simplicity; it is opt-in either way.)
3. Per-Resource toggle only, or would you also want a panel-level
   `->translateModelLabels()` convenience that sets it for all Resources in a
   panel?

Happy to open the PR with Pest tests and docs once you confirm the approach.
