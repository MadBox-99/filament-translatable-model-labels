<?php

declare(strict_types=1);

namespace MadBox99\FilamentTranslatableModelLabels\Tests\Fixtures\Resources;

use MadBox99\FilamentTranslatableModelLabels\Tests\Fixtures\Models\Comment;
use MadBox99\FilamentTranslatableModelLabels\TranslatableFilamentResource;

class CommentResource extends TranslatableFilamentResource
{
    protected static ?string $model = Comment::class;
}
