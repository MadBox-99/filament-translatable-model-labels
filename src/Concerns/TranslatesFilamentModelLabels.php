<?php

namespace MadBox99\FilamentTranslatableModelLabels\Concerns;

use Illuminate\Support\Str;

use function Filament\Support\get_model_label;
use function Filament\Support\locale_has_pluralization;

/**
 * Resolves a Filament Resource's model labels through Laravel's `__()` translation,
 * keyed off the auto-derived model name. Intended for use inside a class that
 * extends `Filament\Resources\Resource`.
 */
trait TranslatesFilamentModelLabels
{
    public static function getModelLabel(): string
    {
        if (filled($label = static::$modelLabel)) {
            return $label;
        }

        return static::translateModelLabel(get_model_label(static::getModel()));
    }

    public static function getPluralModelLabel(): string
    {
        if (filled($label = static::$pluralModelLabel)) {
            return $label;
        }

        $base = static::$modelLabel ?? get_model_label(static::getModel());

        return static::translateModelLabel(
            locale_has_pluralization() ? Str::plural($base) : $base,
        );
    }

    /**
     * Translate one derived label, honouring the configured key case.
     *
     * The lookup key may be capitalised (`key_case` => `'ucfirst'`) so that apps whose
     * translation namespace is capitalised — the case Filament itself produces for field
     * labels via `Str::headline()` — do not need a second, lower-cased copy of every noun.
     * The FALLBACK deliberately stays Filament's own lower-cased label: `__()` echoes the
     * key back when nothing is translated, and returning `Issue` there would silently
     * change untranslated locales from "Create issue" to "Create Issue".
     */
    protected static function translateModelLabel(string $label): string
    {
        $key = static::modelLabelTranslationKey($label);

        $translated = __($key);

        return $translated === $key ? $label : $translated;
    }

    protected static function modelLabelTranslationKey(string $label): string
    {
        return config('filament-translatable-model-labels.key_case') === 'ucfirst'
            ? Str::ucfirst($label)
            : $label;
    }
}
