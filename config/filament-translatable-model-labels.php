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

    /*
    |--------------------------------------------------------------------------
    | Translation key case
    |--------------------------------------------------------------------------
    |
    | Which case the lookup key is derived in. `lower` (the default) follows
    | Filament's own convention: `Customer` becomes `__('customer')`, matching the
    | lower-cased label Filament renders and then capitalises at the call site.
    |
    | Set it to `ucfirst` when your app's translation namespace is already
    | capitalised — which is what Filament produces for FIELD labels, since
    | `->translateLabel()` keys off `Str::headline()` and yields `Customer`. Without
    | this, such an app needs a second, lower-cased copy of every noun it has
    | already translated.
    |
    | Only the lookup key changes. An untranslated locale still falls back to
    | Filament's lower-cased label, so it renders exactly as stock Filament either
    | way.
    |
    | Supported: "lower", "ucfirst"
    |
    */

    'key_case' => 'lower',

];
