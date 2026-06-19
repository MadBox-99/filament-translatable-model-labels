<?php

use Illuminate\Support\Facades\App;
use MadBox99\FilamentTranslatableModelLabels\Tests\Fixtures\Resources\BlogPostResource;
use MadBox99\FilamentTranslatableModelLabels\Tests\Fixtures\Resources\IssueResource;
use MadBox99\FilamentTranslatableModelLabels\Tests\Fixtures\Resources\IssueResourceWithExplicitLabel;

it('translates the singular model label from a json translation', function () {
    App::setLocale('hu');

    expect(IssueResource::getModelLabel())->toBe('probléma');
});

it('translates the plural model label from a json translation', function () {
    App::setLocale('hu');

    expect(IssueResource::getPluralModelLabel())->toBe('problémák');
});

it('falls back to the humanised label when no translation exists', function () {
    App::setLocale('en');

    expect(IssueResource::getModelLabel())->toBe('issue');
    expect(IssueResource::getPluralModelLabel())->toBe('issues');
});

it('lets an explicit model label win over translation', function () {
    App::setLocale('hu');

    expect(IssueResourceWithExplicitLabel::getModelLabel())->toBe('Custom singular');
    expect(IssueResourceWithExplicitLabel::getPluralModelLabel())->toBe('Custom plural');
});

it('translates multi-word model labels using spaced keys', function () {
    App::setLocale('hu');

    expect(BlogPostResource::getModelLabel())->toBe('blogbejegyzés');
    expect(BlogPostResource::getPluralModelLabel())->toBe('blogbejegyzések');
});
