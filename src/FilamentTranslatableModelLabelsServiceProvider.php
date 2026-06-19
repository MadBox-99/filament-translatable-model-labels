<?php

declare(strict_types=1);

namespace MadBox99\FilamentTranslatableModelLabels;

use Filament\Commands\FileGenerators\Resources\ResourceClassGenerator;
use Illuminate\Support\ServiceProvider;
use MadBox99\FilamentTranslatableModelLabels\Generators\TranslatableResourceClassGenerator;

class FilamentTranslatableModelLabelsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/filament-translatable-model-labels.php',
            'filament-translatable-model-labels',
        );

        if (config('filament-translatable-model-labels.inject_trait_into_generated_resources', false)) {
            $this->app->bind(ResourceClassGenerator::class, TranslatableResourceClassGenerator::class);
        }
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/filament-translatable-model-labels.php' => config_path('filament-translatable-model-labels.php'),
            ], 'filament-translatable-model-labels-config');
        }
    }
}
