<?php

namespace MadBox99\FilamentTranslatableModelLabels\Tests;

use MadBox99\FilamentTranslatableModelLabels\FilamentTranslatableModelLabelsServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            FilamentTranslatableModelLabelsServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['translator']->addJsonPath(__DIR__.'/Fixtures/lang');
    }
}
