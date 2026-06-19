<?php

declare(strict_types=1);

use Illuminate\Support\Facades\App;
use MadBox99\FilamentTranslatableModelLabels\Tests\Fixtures\Resources\CommentResource;

it('translates labels for a resource extending the base class', function () {
    App::setLocale('hu');

    expect(CommentResource::getModelLabel())->toBe('hozzászólás');
    expect(CommentResource::getPluralModelLabel())->toBe('hozzászólások');
});

it('falls back for the base class when no translation exists', function () {
    App::setLocale('en');

    expect(CommentResource::getModelLabel())->toBe('comment');
    expect(CommentResource::getPluralModelLabel())->toBe('comments');
});
