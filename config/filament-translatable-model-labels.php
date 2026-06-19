<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Inject the trait into generated resources
    |--------------------------------------------------------------------------
    |
    | When true, `php artisan make:filament-resource` automatically adds the
    | `TranslatesFilamentModelLabels` trait to every generated resource, so its
    | model labels resolve through translations without any manual editing.
    | The generated resource still extends the original Filament `Resource`.
    |
    | On by default — the whole point of this package is that labels translate
    | automatically, so you don't need to publish this config to benefit. The
    | trait is a no-op when no translation exists (it returns the stock label),
    | so it is harmless even on resources you don't translate. Publish this file
    | and set it to false if you prefer to add the trait manually instead.
    |
    */

    'inject_trait_into_generated_resources' => true,

];
