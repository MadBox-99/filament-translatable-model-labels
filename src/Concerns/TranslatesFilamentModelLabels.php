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
        return static::$modelLabel ?? __(get_model_label(static::getModel()));
    }

    public static function getPluralModelLabel(): string
    {
        if (filled($label = static::$pluralModelLabel)) {
            return $label;
        }

        $base = static::$modelLabel ?? get_model_label(static::getModel());

        return __(locale_has_pluralization() ? Str::plural($base) : $base);
    }
}
