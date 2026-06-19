<?php

declare(strict_types=1);

namespace MadBox99\FilamentTranslatableModelLabels;

use Filament\Resources\Resource;
use MadBox99\FilamentTranslatableModelLabels\Concerns\TranslatesFilamentModelLabels;

abstract class TranslatableFilamentResource extends Resource
{
    use TranslatesFilamentModelLabels;
}
