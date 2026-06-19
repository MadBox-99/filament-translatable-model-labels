<?php

namespace MadBox99\FilamentTranslatableModelLabels\Tests\Fixtures\Resources;

use Filament\Resources\Resource;
use MadBox99\FilamentTranslatableModelLabels\Concerns\TranslatesFilamentModelLabels;
use MadBox99\FilamentTranslatableModelLabels\Tests\Fixtures\Models\Issue;

class IssueResourceWithExplicitLabel extends Resource
{
    use TranslatesFilamentModelLabels;

    protected static ?string $model = Issue::class;

    protected static ?string $modelLabel = 'Custom singular';

    protected static ?string $pluralModelLabel = 'Custom plural';
}
