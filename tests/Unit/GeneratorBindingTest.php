<?php

declare(strict_types=1);

use Filament\Commands\FileGenerators\Resources\ResourceClassGenerator;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use MadBox99\FilamentTranslatableModelLabels\FilamentTranslatableModelLabelsServiceProvider;
use MadBox99\FilamentTranslatableModelLabels\Generators\TranslatableResourceClassGenerator;

function registerProviderWithFlag(bool $flag): Container
{
    $app = new Container;
    Container::setInstance($app);
    $app->instance('config', new Repository([
        'filament-translatable-model-labels' => [
            'inject_trait_into_generated_resources' => $flag,
        ],
    ]));

    (new FilamentTranslatableModelLabelsServiceProvider($app))->register();

    return $app;
}

afterEach(fn () => Container::setInstance(null));

it('binds the custom resource generator when the flag is on', function () {
    $app = registerProviderWithFlag(true);

    expect($app->bound(ResourceClassGenerator::class))->toBeTrue()
        ->and($app->getBindings()[ResourceClassGenerator::class]['concrete'])
        ->not->toBeNull();
});

it('resolves the custom generator subclass when the flag is on', function () {
    $app = registerProviderWithFlag(true);

    $generator = $app->make(ResourceClassGenerator::class, [
        'fqn' => 'App\\Filament\\Resources\\OrderResource',
        'modelFqn' => 'App\\Models\\Order',
        'pageRoutes' => [],
        'formSchemaFqn' => null,
        'infolistSchemaFqn' => null,
        'tableFqn' => null,
        'clusterFqn' => null,
        'parentResourceFqn' => null,
        'recordTitleAttribute' => null,
        'hasViewOperation' => false,
        'isGenerated' => false,
        'isSoftDeletable' => false,
        'isSimple' => true,
    ]);

    expect($generator)->toBeInstanceOf(TranslatableResourceClassGenerator::class);
});

it('does not bind the generator when the flag is off', function () {
    $app = registerProviderWithFlag(false);

    expect($app->bound(ResourceClassGenerator::class))->toBeFalse();
});
