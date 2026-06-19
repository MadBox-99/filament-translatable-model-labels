<?php

declare(strict_types=1);

namespace MadBox99\FilamentTranslatableModelLabels\Generators;

use Filament\Commands\FileGenerators\Resources\ResourceClassGenerator;
use MadBox99\FilamentTranslatableModelLabels\Concerns\TranslatesFilamentModelLabels;
use Nette\PhpGenerator\ClassType;

/**
 * Drop-in replacement for Filament's resource generator that adds the
 * `TranslatesFilamentModelLabels` trait to every generated resource. The
 * generated class still extends the original Filament `Resource`.
 */
class TranslatableResourceClassGenerator extends ResourceClassGenerator
{
    /**
     * @return array<string>
     */
    public function getImports(): array
    {
        return [
            ...parent::getImports(),
            TranslatesFilamentModelLabels::class,
        ];
    }

    protected function addTraitsToClass(ClassType $class): void
    {
        parent::addTraitsToClass($class);

        $class->addTrait(TranslatesFilamentModelLabels::class);
    }
}
