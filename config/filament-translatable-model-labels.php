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
    | Off by default so installing this package never changes how other apps
    | generate resources — opt in per application.
    |
    */

    'inject_trait_into_generated_resources' => false,

];
